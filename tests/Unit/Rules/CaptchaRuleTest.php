<?php

// 檔案位置：tests/Unit/Rules/CaptchaRuleTest.php
// 執行指令：vendor/bin/pest --filter=CaptchaRuleTest

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use MrJin\Captcha\Rules\CaptchaRule;

beforeEach(function () {
    config()->set('captcha.default', 'recaptcha');
    config()->set('captcha.providers.recaptcha', [
        'site_key' => 'test-site-key',
        'secret_key' => 'test-secret-key',
        'version' => 'v2',
    ]);
});

it('passes validation when captcha verification succeeds', function () {
    // Arrange
    Http::fake([
        'www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true]),
    ]);

    // Act
    $validator = Validator::make(
        ['captcha_token' => 'valid-token'],
        ['captcha_token' => ['required', new CaptchaRule]],
    );

    // Assert
    expect($validator->passes())->toBeTrue();
});

it('fails validation when captcha verification fails', function () {
    // Arrange
    Http::fake([
        'www.google.com/recaptcha/api/siteverify' => Http::response(['success' => false]),
    ]);

    // Act
    $validator = Validator::make(
        ['captcha_token' => 'invalid-token'],
        ['captcha_token' => ['required', new CaptchaRule]],
    );

    // Assert
    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('captcha_token'))->toBeTrue();
});

it('uses specified driver for validation', function () {
    // Arrange
    config()->set('captcha.providers.turnstile', [
        'site_key' => 'turnstile-site-key',
        'secret_key' => 'turnstile-secret-key',
    ]);

    Http::fake([
        'challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => true]),
    ]);

    // Act
    $validator = Validator::make(
        ['captcha_token' => 'valid-token'],
        ['captcha_token' => ['required', new CaptchaRule('turnstile')]],
    );

    // Assert
    expect($validator->passes())->toBeTrue();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'cloudflare.com');
    });
});

it('throws exception for unsupported driver', function () {
    new CaptchaRule('invalid-driver');
})->throws(InvalidArgumentException::class, 'Unsupported captcha driver [invalid-driver]');
