<?php

// 檔案位置：tests/Unit/Providers/HcaptchaProviderTest.php
// 執行指令：vendor/bin/pest --filter=HcaptchaProviderTest

use Illuminate\Support\Facades\Http;
use MrJin\Captcha\Providers\HcaptchaProvider;

function makeHcaptchaProvider(array $overrides = []): HcaptchaProvider
{
    return new HcaptchaProvider(array_merge([
        'site_key' => 'test-site-key',
        'secret_key' => 'test-secret-key',
    ], $overrides));
}

it('verifies token successfully', function () {
    // Arrange
    Http::fake([
        'api.hcaptcha.com/siteverify' => Http::response(['success' => true]),
    ]);

    // Act
    $result = makeHcaptchaProvider()->verify('valid-token', '127.0.0.1');

    // Assert
    expect($result)->toBeTrue();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.hcaptcha.com/siteverify'
            && $request['secret'] === 'test-secret-key'
            && $request['response'] === 'valid-token'
            && $request['remoteip'] === '127.0.0.1';
    });
});

it('returns false when verification fails', function () {
    // Arrange
    Http::fake([
        'api.hcaptcha.com/siteverify' => Http::response(['success' => false]),
    ]);

    // Act
    $result = makeHcaptchaProvider()->verify('invalid-token');

    // Assert
    expect($result)->toBeFalse();
});

it('returns false for empty token', function () {
    // Arrange
    Http::fake();

    // Act
    $result = makeHcaptchaProvider()->verify('');

    // Assert
    expect($result)->toBeFalse();
    Http::assertNothingSent();
});

it('returns false when connection fails', function () {
    // Arrange
    Http::fake([
        'api.hcaptcha.com/siteverify' => fn () => throw new \Illuminate\Http\Client\ConnectionException,
    ]);

    // Act
    $result = makeHcaptchaProvider()->verify('valid-token');

    // Assert
    expect($result)->toBeFalse();
});

it('throws exception when site_key is missing', function () {
    new HcaptchaProvider(['secret_key' => 'test']);
})->throws(InvalidArgumentException::class, 'Hcaptcha provider is missing [site_key].');

it('throws exception when secret_key is missing', function () {
    new HcaptchaProvider(['site_key' => 'test']);
})->throws(InvalidArgumentException::class, 'Hcaptcha provider is missing [secret_key].');
