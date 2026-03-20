<?php

// 檔案位置：tests/Unit/Providers/TurnstileProviderTest.php
// 執行指令：vendor/bin/pest --filter=TurnstileProviderTest

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use MrJin\Captcha\Providers\TurnstileProvider;

function makeTurnstileProvider(array $overrides = []): TurnstileProvider
{
    return new TurnstileProvider(array_merge([
        'site_key' => 'test-site-key',
        'secret_key' => 'test-secret-key',
    ], $overrides));
}

it('verifies token successfully', function () {
    // Arrange
    Http::fake([
        'challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => true]),
    ]);

    // Act
    $result = makeTurnstileProvider()->verify('valid-token', '127.0.0.1');

    // Assert
    expect($result)->toBeTrue();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
            && $request['secret'] === 'test-secret-key'
            && $request['response'] === 'valid-token'
            && $request['remoteip'] === '127.0.0.1';
    });
});

it('returns false when verification fails', function () {
    // Arrange
    Http::fake([
        'challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => false]),
    ]);

    // Act
    $result = makeTurnstileProvider()->verify('invalid-token');

    // Assert
    expect($result)->toBeFalse();
});

it('returns false for empty token', function () {
    // Arrange
    Http::fake();

    // Act
    $result = makeTurnstileProvider()->verify('');

    // Assert
    expect($result)->toBeFalse();
    Http::assertNothingSent();
});

it('returns false when connection fails', function () {
    // Arrange
    Http::fake([
        'challenges.cloudflare.com/turnstile/v0/siteverify' => fn () => throw new ConnectionException,
    ]);

    // Act
    $result = makeTurnstileProvider()->verify('valid-token');

    // Assert
    expect($result)->toBeFalse();
});

it('throws exception when site_key is missing', function () {
    new TurnstileProvider(['secret_key' => 'test']);
})->throws(InvalidArgumentException::class, 'Turnstile provider is missing [site_key].');

it('throws exception when secret_key is missing', function () {
    new TurnstileProvider(['site_key' => 'test']);
})->throws(InvalidArgumentException::class, 'Turnstile provider is missing [secret_key].');
