<?php

namespace MrJin\Captcha;

use Illuminate\Support\Manager;
use InvalidArgumentException;
use MrJin\Captcha\Contracts\CaptchaProviderInterface;
use MrJin\Captcha\Providers\HcaptchaProvider;
use MrJin\Captcha\Providers\RecaptchaProvider;
use MrJin\Captcha\Providers\TurnstileProvider;

class CaptchaManager extends Manager implements CaptchaProviderInterface
{
    protected array $supportedDrivers = ['recaptcha', 'turnstile', 'hcaptcha'];

    public function getSupportedDrivers(): array
    {
        return $this->supportedDrivers;
    }

    public function getDefaultDriver(): string
    {
        return $this->config->get('captcha.default', 'recaptcha');
    }

    public function createRecaptchaDriver(): RecaptchaProvider
    {
        return new RecaptchaProvider($this->getProviderConfig('recaptcha'));
    }

    public function createTurnstileDriver(): TurnstileProvider
    {
        return new TurnstileProvider($this->getProviderConfig('turnstile'));
    }

    public function createHcaptchaDriver(): HcaptchaProvider
    {
        return new HcaptchaProvider($this->getProviderConfig('hcaptcha'));
    }

    public function isEnabled(): bool
    {
        return $this->config->get('captcha.enabled', true);
    }

    public function verify(string $token, ?string $ip = null): bool
    {
        if (! $this->isEnabled()) {
            return true;
        }

        return $this->driver()->verify($token, $ip);
    }

    protected function getProviderConfig(string $name): array
    {
        $config = $this->config->get("captcha.providers.{$name}");

        throw_unless($config, InvalidArgumentException::class, "Captcha provider [{$name}] is not configured.");

        return $config;
    }
}
