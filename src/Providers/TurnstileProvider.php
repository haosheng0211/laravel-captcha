<?php

namespace MrJin\Captcha\Providers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use MrJin\Captcha\Contracts\CaptchaProviderInterface;

class TurnstileProvider implements CaptchaProviderInterface
{
    protected string $siteKey;

    protected string $secretKey;

    public function __construct(array $config)
    {
        throw_unless($config['site_key'] ?? null, InvalidArgumentException::class, 'Turnstile provider is missing [site_key].');
        throw_unless($config['secret_key'] ?? null, InvalidArgumentException::class, 'Turnstile provider is missing [secret_key].');

        $this->siteKey = $config['site_key'];
        $this->secretKey = $config['secret_key'];
    }

    public function verify(string $token, ?string $ip = null): bool
    {
        if (empty($token)) {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(5)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', array_filter([
                'secret' => $this->secretKey,
                'response' => $token,
                'remoteip' => $ip,
            ]));
        } catch (ConnectionException) {
            return false;
        }

        return $response->json('success', false);
    }
}
