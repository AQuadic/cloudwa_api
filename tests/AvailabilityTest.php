<?php

use AQuadic\Cloudwa\Cloudwa;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

it('returns true when number exists on WhatsApp and session is active', function () {
    Http::fake([
        'https://cloudwa.net/api/v2/sessions/check_availability*' => Http::response([
            'id' => '201129261195@c.us',
            'isBusiness' => false,
            'canReceiveMessage' => true,
            'numberExists' => true,
            'status' => 200,
        ], 200),
    ]);

    $cloudwa = (new Cloudwa)->phone('201129261195');

    expect($cloudwa->checkAvailability())->toBeTrue();
});

it('returns false without throwing exception when number does not exist on WhatsApp (404)', function () {
    Http::fake([
        'https://cloudwa.net/api/v2/sessions/check_availability*' => Http::response([
            'id' => '966593550691@c.us',
            'isBusiness' => false,
            'canReceiveMessage' => false,
            'numberExists' => false,
            'status' => 404,
        ], 404),
    ]);

    $cloudwa = (new Cloudwa)->phone('966593550691')->throw(true);

    expect($cloudwa->checkAvailability())->toBeFalse();
});

it('throws exception on 500 server error when throwOnException is enabled', function () {
    Http::fake([
        'https://cloudwa.net/api/v2/sessions/check_availability*' => Http::response([
            'message' => 'Server Error',
        ], 500),
    ]);

    $cloudwa = (new Cloudwa)->phone('201129261195')->throw(true);

    expect(fn () => $cloudwa->checkAvailability())->toThrow(RequestException::class);
});

it('returns false on 500 server error when throwOnException is disabled', function () {
    Http::fake([
        'https://cloudwa.net/api/v2/sessions/check_availability*' => Http::response([
            'message' => 'Server Error',
        ], 500),
    ]);

    $cloudwa = (new Cloudwa)->phone('201129261195')->throw(false);

    expect($cloudwa->checkAvailability())->toBeFalse();
});

it('throws connection exception on timeout when throwOnException is enabled', function () {
    Http::fake([
        'https://cloudwa.net/api/v2/sessions/check_availability*' => fn () => throw new ConnectionException('Connection timed out'),
    ]);

    $cloudwa = (new Cloudwa)->phone('201129261195')->throw(true);

    expect(fn () => $cloudwa->checkAvailability())->toThrow(ConnectionException::class);
});
