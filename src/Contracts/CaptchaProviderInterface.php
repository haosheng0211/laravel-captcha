<?php

namespace MrJin\Captcha\Contracts;

interface CaptchaProviderInterface
{
    public function verify(string $token, ?string $ip = null): bool;
}
