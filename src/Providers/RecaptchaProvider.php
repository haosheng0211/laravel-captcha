<?php

namespace MrJin\Captcha\Providers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use MrJin\Captcha\Contracts\CaptchaProviderInterface;

class RecaptchaProvider implements CaptchaProviderInterface
{
    protected string $siteKey;

    protected string $secretKey;

    protected string $version;

    protected float $score;

    public function __construct(array $config)
    {
        throw_unless($config['site_key'] ?? null, InvalidArgumentException::class, 'Recaptcha provider is missing [site_key].');
        throw_unless($config['secret_key'] ?? null, InvalidArgumentException::class, 'Recaptcha provider is missing [secret_key].');

        $this->siteKey = $config['site_key'];
        $this->secretKey = $config['secret_key'];
        $this->version = $config['version'] ?? 'v2';
        $this->score = $config['score'] ?? 0.5;
    }

    public function verify(string $token, ?string $ip = null): bool
    {
        if (empty($token)) {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(5)->post('https://www.google.com/recaptcha/api/siteverify', array_filter([
                'secret' => $this->secretKey,
                'response' => $token,
                'remoteip' => $ip,
            ]));
        } catch (ConnectionException) {
            return false;
        }

        $data = $response->json();

        if (! ($data['success'] ?? false)) {
            return false;
        }

        if ($this->version === 'v3') {
            return ($data['score'] ?? 0) >= $this->score;
        }

        return true;
    }
}
