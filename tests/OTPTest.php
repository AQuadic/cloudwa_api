<?php

use AQuadic\Cloudwa\Cloudwa;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

it('parses private OTP numbers correctly from different configurations', function () {
    // 1. Single string number
    config()->set('cloudwa.otp.private', '966500000000');
    $numbers = Cloudwa::getPrivateOTPNumbers();
    expect($numbers)->toHaveCount(1)
        ->and($numbers->first())->toBe('966500000000');

    // 2. Comma separated string numbers
    config()->set('cloudwa.otp.private', '966500000000, 966511111111,966522222222');
    $numbers = Cloudwa::getPrivateOTPNumbers();
    expect($numbers)->toHaveCount(3)
        ->and($numbers->toArray())->toBe(['966500000000', '966511111111', '966522222222']);

    // 3. Array of numbers
    config()->set('cloudwa.otp.private', ['966500000000', '966511111111']);
    $numbers = Cloudwa::getPrivateOTPNumbers();
    expect($numbers)->toHaveCount(2)
        ->and($numbers->toArray())->toBe(['966500000000', '966511111111']);

    // 4. Empty configuration
    config()->set('cloudwa.otp.private', null);
    $numbers = Cloudwa::getPrivateOTPNumbers();
    expect($numbers)->toBeEmpty();
});

it('generates callback using only private numbers when shared OTP is disabled', function () {
    config()->set('cloudwa.otp.shared', false);
    config()->set('cloudwa.otp.private', '966500000000,966511111111');
    config()->set('cloudwa.team_id', 'test-team');

    $callback = Cloudwa::generateWaCallback('ref123', 'code123');

    expect($callback['reference'])->toBe('ref123')
        ->and($callback['message'])->toBe('OTP:test-team:code123')
        ->and(['966500000000', '966511111111'])->toContain($callback['phone'])
        ->and($callback['scheme'])->toBe("whatsapp://send?text=OTP:test-team:code123&phone={$callback['phone']}&abid={$callback['phone']}")
        ->and($callback['url'])->toBe("https://wa.me/{$callback['phone']}?text=OTP:test-team:code123");
});

it('generates callback merging shared and private numbers when shared OTP is enabled', function () {
    config()->set('cloudwa.otp.shared', true);
    config()->set('cloudwa.otp.private', ['private1', 'private2']);
    config()->set('cloudwa.team_id', 'test-team');

    // Mock shared numbers using cache to avoid outbound HTTP request
    cache()->put('cloudwa-shared-otp-numbers', ['shared1', 'shared2'], 60);

    $callback = Cloudwa::generateWaCallback('ref123', 'code123');

    expect($callback['reference'])->toBe('ref123')
        ->and($callback['message'])->toBe('OTP:test-team:code123')
        ->and(['private1', 'private2', 'shared1', 'shared2'])->toContain($callback['phone']);
});

it('respects throwOnException option in sendMessage and sendOTP', function () {
    $cloudwa = new Cloudwa;
    $cloudwa->phone('966500000000')
        ->message('Test Message')
        ->throw(true);

    // Mock HTTP failure to verify exception is thrown
    Http::fake(function () {
        return Http::response('Server Error', 500);
    });

    expect(fn () => $cloudwa->sendMessage())->toThrow(RequestException::class);
    expect(fn () => $cloudwa->sendOTP())->toThrow(RequestException::class);
});
