# Laravel Captcha

[![Latest Version on Packagist](https://img.shields.io/packagist/v/haosheng0211/laravel-captcha.svg?style=flat-square)](https://packagist.org/packages/haosheng0211/laravel-captcha)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/haosheng0211/laravel-captcha/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/haosheng0211/laravel-captcha/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/haosheng0211/laravel-captcha/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/haosheng0211/laravel-captcha/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/haosheng0211/laravel-captcha.svg?style=flat-square)](https://packagist.org/packages/haosheng0211/laravel-captcha)

A Laravel package that provides a unified API for integrating multiple captcha services, including Google reCAPTCHA (v2 & v3), Cloudflare Turnstile, and hCaptcha. Easily switch between providers with minimal code changes.

## Installation

You can install the package via composer:

```bash
composer require haosheng0211/laravel-captcha
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-captcha-config"
```

This is the contents of the published config file:

```php
return [

    'enabled' => env('CAPTCHA_ENABLED', true),

    'default' => env('CAPTCHA_DRIVER', 'recaptcha'),

    'providers' => [
        'recaptcha' => [
            'version' => env('RECAPTCHA_VERSION', 'v2'),
            'site_key' => env('RECAPTCHA_SITE_KEY'),
            'secret_key' => env('RECAPTCHA_SECRET_KEY'),
            'score' => 0.5,
        ],
        'turnstile' => [
            'site_key' => env('TURNSTILE_SITE_KEY'),
            'secret_key' => env('TURNSTILE_SECRET_KEY'),
        ],
        'hcaptcha' => [
            'site_key' => env('HCAPTCHA_SITE_KEY'),
            'secret_key' => env('HCAPTCHA_SECRET_KEY'),
        ],
    ],

];
```

## Configuration

Add the following environment variables to your `.env` file based on the captcha provider you want to use:

```dotenv
# Global settings
CAPTCHA_ENABLED=true
CAPTCHA_DRIVER=recaptcha

# Google reCAPTCHA
RECAPTCHA_VERSION=v2
RECAPTCHA_SITE_KEY=your-site-key
RECAPTCHA_SECRET_KEY=your-secret-key

# Cloudflare Turnstile
TURNSTILE_SITE_KEY=your-site-key
TURNSTILE_SECRET_KEY=your-secret-key

# hCaptcha
HCAPTCHA_SITE_KEY=your-site-key
HCAPTCHA_SECRET_KEY=your-secret-key
```

## Usage

### Using the Facade

```php
use MrJin\Captcha\Facades\Captcha;

// Verify using the default driver
$result = Captcha::verify($request->input('captcha_token'), $request->ip());

// Verify using a specific driver
$result = Captcha::driver('turnstile')->verify($token, $ip);

// Check if captcha is enabled
if (Captcha::isEnabled()) {
    // ...
}
```

### Using the Validation Rule

```php
use MrJin\Captcha\Rules\CaptchaRule;

$request->validate([
    'captcha_token' => ['required', new CaptchaRule],
]);

// With a specific driver
$request->validate([
    'captcha_token' => ['required', new CaptchaRule('turnstile')],
]);
```

### Supported Drivers

| Driver | Provider | Versions |
|---|---|---|
| `recaptcha` | Google reCAPTCHA | v2, v3 |
| `turnstile` | Cloudflare Turnstile | - |
| `hcaptcha` | hCaptcha | - |

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Mr.Jin](https://github.com/haosheng0211)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
