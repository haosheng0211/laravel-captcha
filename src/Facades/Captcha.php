<?php

namespace MrJin\Captcha\Facades;

use Illuminate\Support\Facades\Facade;
use MrJin\Captcha\CaptchaManager;
use MrJin\Captcha\Contracts\CaptchaProviderInterface;

/**
 * @method static CaptchaProviderInterface driver(?string $driver = null)
 * @method static bool verify(string $token, ?string $ip = null)
 * @method static bool isEnabled()
 * @method static string getDefaultDriver()
 *
 * @see CaptchaManager
 */
class Captcha extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CaptchaManager::class;
    }
}
