<?php

namespace AQuadic\Cloudwa\Channels;

use AQuadic\Cloudwa\Cloudwa;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Notifications\Notification;

class CloudWaOTPChannel
{
    /**
     * Send the given notification.
     *
     * @throws ConnectionException
     * @throws Exception
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        $message = match (true) {
            method_exists($notification, 'toCloudWaOTP') => $notification->toCloudWaOTP($notifiable),
            method_exists($notification, 'toCloudWa') => $notification->toCloudWa($notifiable),
            default => throw new \Exception('toCloudWa or toCloudWaOTP Method not added yet in notification class'),
        };

        (new Cloudwa)
            ->session($message['uuid'] ?? $message['session_uuid'] ?? null)
            ->file($message['image'] ?? $message['file'] ?? null)
            ->phone($message['phones'] ?? $message['phone'] ?? null)
            ->type($message['type'] ?? null)
            ->message(
                $message['message'] ?? $message['text'] ?? $message['otp'] ?? $message['code'] ?? null,
                $message['reference_number'] ?? $message['reference'] ?? null,
            )
            ->throw()
            ->sendOTP();
    }
}
