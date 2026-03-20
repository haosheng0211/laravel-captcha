<?php

// 檔案位置：tests/Unit/CaptchaManagerTest.php
// 執行指令：vendor/bin/pest --filter=CaptchaManagerTest

use MrJin\Captcha\CaptchaManager;
use MrJin\Captcha\Providers\HcaptchaProvider;
use MrJin\Captcha\Providers\RecaptchaProvider;
use MrJin\Captcha\Providers\TurnstileProvider;

it('returns default driver from config', function () {
    // Arrange
    config()->set('captcha.default', 'turnstile');
    config()->set('captcha.providers.turnstile', [
        'site_key' => 'test-site-key',
        'secret_key' => 'test-secret-key',
    ]);

    // Act
    $manager = app(CaptchaManager::class);

    // Assert
    expect($manager->getDefaultDriver())->toBe('turnstile');
});

it('creates recaptcha driver', function () {
    // Arrange
    config()->set('captcha.providers.recaptcha', [
        'site_key' => 'test-site-key',
        'secret_key' => 'test-secret-key',
        'version' => 'v2',
    ]);

    // Act
    $driver = app(CaptchaManager::class)->driver('recaptcha');

    // Assert
    expect($driver)->toBeInstanceOf(RecaptchaProvider::class);
});

it('creates turnstile driver', function () {
    // Arrange
    config()->set('captcha.providers.turnstile', [
        'site_key' => 'test-site-key',
        'secret_key' => 'test-secret-key',
    ]);

    // Act
    $driver = app(CaptchaManager::class)->driver('turnstile');

    // Assert
    expect($driver)->toBeInstanceOf(TurnstileProvider::class);
});

it('creates hcaptcha driver', function () {
    // Arrange
    config()->set('captcha.providers.hcaptcha', [
        'site_key' => 'test-site-key',
        'secret_key' => 'test-secret-key',
    ]);

    // Act
    $driver = app(CaptchaManager::class)->driver('hcaptcha');

    // Assert
    expect($driver)->toBeInstanceOf(HcaptchaProvider::class);
});

it('throws exception for unsupported driver', function () {
    // Act & Assert
    app(CaptchaManager::class)->driver('unsupported');
})->throws(InvalidArgumentException::class);

it('throws exception when provider config is missing', function () {
    // Arrange
    config()->set('captcha.providers.recaptcha', null);

    // Act & Assert
    app(CaptchaManager::class)->driver('recaptcha');
})->throws(InvalidArgumentException::class, 'Captcha provider [recaptcha] is not configured.');

it('returns supported drivers list', function () {
    // Act
    $drivers = app(CaptchaManager::class)->getSupportedDrivers();

    // Assert
    expect($drivers)->toBe(['recaptcha', 'turnstile', 'hcaptcha']);
});

it('skips verification when disabled', function () {
    // Arrange
    config()->set('captcha.enabled', false);

    Http::fake();

    // Act
    $result = app(CaptchaManager::class)->verify('any-token');

    // Assert
    expect($result)->toBeTrue();
    Http::assertNothingSent();
});

it('delegates verify to default driver', function () {
    // Arrange
    config()->set('captcha.default', 'recaptcha');
    config()->set('captcha.providers.recaptcha', [
        'site_key' => 'test-site-key',
        'secret_key' => 'test-secret-key',
    ]);

    Http::fake([
        'www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true]),
    ]);

    // Act
    $result = app(CaptchaManager::class)->verify('valid-token', '127.0.0.1');

    // Assert
    expect($result)->toBeTrue();
});
