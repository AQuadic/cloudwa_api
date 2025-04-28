<?php

namespace AQuadic\Cloudwa;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class Cloudwa
{
    protected ?string $sessionUuid;

    protected ?string $message;

    protected ?string $file = null;

    protected ?string $type = null;

    protected ?string $baseUrl = null;

    protected ?int $timeout = null;

    protected ?array $phones;

    protected ?array $templateParameters = null;

    private array $headers;

    protected Carbon $scheduleAt;

    protected bool $throwOnException;

    protected string $reference;

    public function __construct()
    {
        $this->headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.config('cloudwa.api_token'),
            'Accept' => 'application/json',
        ];

        $this->scheduleAt = now()->utc()->subMinute();
    }

    /**
     * Fetching Shared OTP Numbers.
     */
    public function fetchSharedOTPNumbers(): Collection
    {
        try {
            return cache()->remember('cloudwa-shared-otp-numbers', 60 * 60, function () {
                $team = config('cloudwa.team_id');

                return Http::withHeaders($this->headers)
                    ->timeout($this->getTimeout())
                    ->connectTimeout($this->getTimeout())
                    ->throw()
                    ->get("{$this->getBaseUrl()}/api/v3/$team/otps/shared-numbers")
                    ->collect();
            });
        } catch (Exception|\Throwable) {
            return collect();
        }
    }

    public function file(?string $file): static
    {
        $this->file = $file;

        return $this;
    }

    public function message(?string $message, ?string $reference = null): static
    {
        $this->message = $message;
        if (filled($reference)) {
            $this->reference = $reference;
        }

        return $this;
    }

    public function phone(array|string $phone): static
    {
        $this->phones = array_merge($this->phones ?? [], Arr::wrap($phone));

        return $this;
    }

    public function templateParameters(?array $templateParameters): static
    {
        $this->templateParameters = array_merge($this->templateParameters ?? [], Arr::wrap(($templateParameters ?? [])));

        return $this;
    }

    public function session(?string $sessionUuid): static
    {
        $this->sessionUuid = $sessionUuid;

        return $this;
    }

    public function type(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function baseUrl(?string $baseUrl): static
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl ?? 'https://cloudwa.net';
    }

    public function timeout(?int $timeout): static
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function getTimeout(): int
    {
        return $this->timeout ?? 5;
    }

    public function scheduleAt(?Carbon $scheduleAt): static
    {
        $this->scheduleAt = ($scheduleAt ?? now());

        return $this;
    }

    public function token(?string $apiToken): static
    {
        $this->headers['Authorization'] = 'Bearer '.($apiToken ?? config('cloudwa.api_token'));

        return $this;
    }

    public function throw(bool $throwOnException = true): static
    {
        $this->throwOnException = $throwOnException;

        return $this;
    }

    public function clone(): Cloudwa|static
    {
        return clone $this;
    }

    /**
     * Send message to whatsapp
     *
     * @throws ConnectionException
     */
    public function sendMessage(array $inputs = []): Collection
    {
        return collect($this->phones)
            ->filter()
            ->map(fn ($p) => $this->normalizeNumber($p))
            ->map(function ($phone) use ($inputs) {
                $data = [
                    'session_uuid' => $this->sessionUuid ?? config('cloudwa.uuids.default'),
                    'phone' => $phone,
                    'message' => $this->message ?? null,
                    'template_parameters' => $this->templateParameters ?? null,
                    'schedule_at' => $this->scheduleAt,
                    'type' => $this->type ?: (filled($this->file) ? 'IMAGE' : 'TEXT'),
                    'image' => $this->file ?? null,
                    ...$inputs,
                ];

                return rescue(function () use ($data) {
                    $response = Http::withHeaders($this->headers)
                        ->timeout($this->getTimeout())
                        ->connectTimeout($this->getTimeout())
                        ->throw()
                        ->post("{$this->getBaseUrl()}/api/v2/messages/send-message", $data);

                    return [
                        'status' => true,
                        'request' => $data,
                        'response' => $response->json(),
                    ];
                }, function (Exception $exception) use ($data) {
                    return [
                        'status' => false,
                        'request' => $data,
                        'response' => $exception->getMessage(),
                    ];
                });
            });
    }

    /**
     * Check whatsapp phones Availability.
     *
     * @throws ConnectionException
     */
    public function checkAvailability(): bool
    {
        return collect($this->phones)
            ->filter()
            ->map(fn ($p) => $this->normalizeNumber($p))
            ->map(function ($phone) {
                return rescue(function () use ($phone) {
                    $res = Http::withHeaders($this->headers)
                        ->timeout($this->getTimeout())
                        ->connectTimeout($this->getTimeout())
                        ->throw()
                        ->get("{$this->getBaseUrl()}/api/v2/sessions/check_availability", [
                            'session_uuid' => $this->sessionUuid ?? config('cloudwa.uuids.default'),
                            'chat_id' => $phone,
                        ]);

                    return ['status' => true];
                }, function () {
                    return ['status' => false];
                });

            })->where('status', false)->count() == 0;
    }

    /**
     * Notifies Cloudwa about new otp and waits to receive it.
     *
     * @throws ConnectionException
     */
    public function sendOTP(): array|Collection
    {
        $team = config('cloudwa.team_id');

        return collect($this->phones)
            ->filter()
            ->map(fn ($p) => $this->normalizeNumber($p))
            ->map(function ($phone) use ($team) {

                rescue(function () use ($team, $phone) {
                    Http::withHeaders($this->headers)
                        ->timeout($this->getTimeout())
                        ->connectTimeout($this->getTimeout())
                        ->throw()
                        ->post("{$this->getBaseUrl()}/api/v3/$team/otps", [
                            'phone' => $phone,
                            'code' => $this->message,
                            'expires_at' => $this->scheduleAt->addMinutes(10),
                            'reference_number' => $this->reference,
                        ]);

                    return self::generateWaCallback(
                        $this->reference,
                        $this->message,
                    );
                }, function () {
                    return false;
                });

            });
    }

    /**
     * @throws ConnectionException
     */
    public static function generateWaCallback(string $reference, string $code): array
    {
        $team = config('cloudwa.team_id');
        $phone = config('cloudwa.otp.shared')
            ? (new self)->fetchSharedOTPNumbers()
                ->add(config('cloudwa.otp.private'))
                ->filter()
                ->random(1)
                ->first()
            : config('cloudwa.otp.private');

        return [
            'reference' => $reference,
            'message' => 'OTP:'.$team.':'.$code,
            'phone' => $phone,
            'scheme' => "whatsapp://send?text=OTP:$team:$code&phone=$phone&abid=$phone",
            'url' => "https://wa.me/$phone?text=OTP:$team:$code",
        ];
    }

    /**
     * Filtering Out un-needed from the number
     */
    private function normalizeNumber(string $phone): string
    {
        if (! str($phone)->startsWith('https://chat.whatsapp.com/')) {
            // Remove All Non-Digits
            $phone = preg_replace('/\D/', '', $phone);
        }

        // Remove All Spaces
        $phone = preg_replace('/\s/', '', $phone);

        // Remove All Starting Zeros
        return ltrim($phone, '0');
    }
}
