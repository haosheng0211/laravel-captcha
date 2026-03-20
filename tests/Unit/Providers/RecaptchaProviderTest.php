<?php

// 檔案位置：tests/Unit/Providers/RecaptchaProviderTest.php
// 執行指令：vendor/bin/pest --filter=RecaptchaProviderTest

use Illuminate\Support\Facades\Http;
use MrJin\Captcha\Providers\RecaptchaProvider;

function makeRecaptchaProvider(array $overrides = []): RecaptchaProvider
{
    return new RecaptchaProvider(array_merge([
        'site_key' => 'test-site-key',
        'secret_key' => 'test-secret-key',
        'version' => 'v2',
        'score' => 0.5,
    ], $overrides));
}

it('verifies v2 token successfully', function () {
    // Arrange
    Http::fake([
        'www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true]),
    ]);

    // Act
    $result = makeRecaptchaProvider()->verify('valid-token', '127.0.0.1');

    // Assert
    expect($result)->toBeTrue();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://www.google.com/recaptcha/api/siteverify'
            && $request['secret'] === 'test-secret-key'
            && $request['response'] === 'valid-token'
            && $request['remoteip'] === '127.0.0.1';
    });
});

it('returns false when v2 verification fails', function () {
    // Arrange
    Http::fake([
        'www.google.com/recaptcha/api/siteverify' => Http::response(['success' => false]),
    ]);

    // Act
    $result = makeRecaptchaProvider()->verify('invalid-token');

    // Assert
    expect($result)->toBeFalse();
});

it('verifies v3 token with passing score', function () {
    // Arrange
    Http::fake([
        'www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true,
            'score' => 0.9,
        ]),
    ]);

    // Act
    $result = makeRecaptchaProvider(['version' => 'v3', 'score' => 0.5])->verify('valid-token');

    // Assert
    expect($result)->toBeTrue();
});

it('returns false when v3 score is below threshold', function () {
    // Arrange
    Http::fake([
        'www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true,
            'score' => 0.2,
        ]),
    ]);

    // Act
    $result = makeRecaptchaProvider(['version' => 'v3', 'score' => 0.5])->verify('bot-token');

    // Assert
    expect($result)->toBeFalse();
});

it('returns false for empty token', function () {
    // Arrange
    Http::fake();

    // Act
    $result = makeRecaptchaProvider()->verify('');

    // Assert
    expect($result)->toBeFalse();
    Http::assertNothingSent();
});

it('returns false when connection fails', function () {
    // Arrange
    Http::fake([
        'www.google.com/recaptcha/api/siteverify' => fn () => throw new \Illuminate\Http\Client\ConnectionException,
    ]);

    // Act
    $result = makeRecaptchaProvider()->verify('valid-token');

    // Assert
    expect($result)->toBeFalse();
});

it('throws exception when site_key is missing', function () {
    new RecaptchaProvider(['secret_key' => 'test']);
})->throws(InvalidArgumentException::class, 'Recaptcha provider is missing [site_key].');

it('throws exception when secret_key is missing', function () {
    new RecaptchaProvider(['site_key' => 'test']);
})->throws(InvalidArgumentException::class, 'Recaptcha provider is missing [secret_key].');
