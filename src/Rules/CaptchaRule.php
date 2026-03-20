<?php

namespace MrJin\Captcha\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use MrJin\Captcha\CaptchaManager;

class CaptchaRule implements ValidationRule
{
    public function __construct(protected ?string $driver = null)
    {
        if ($this->driver !== null) {
            $captcha = app(CaptchaManager::class);

            throw_unless(
                in_array($this->driver, $captcha->getSupportedDrivers()),
                \InvalidArgumentException::class,
                "Unsupported captcha driver [{$this->driver}]. Supported: ".implode(', ', $captcha->getSupportedDrivers()).'.',
            );
        }
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $captcha = app(CaptchaManager::class);
        $driver = $this->driver ? $captcha->driver($this->driver) : $captcha;

        if (! $driver->verify($value, request()->ip())) {
            $fail('captcha::validation.captcha_failed')->translate();
        }
    }
}
