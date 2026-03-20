<?php

namespace MrJin\Captcha;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CaptchaServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-captcha')
            ->hasConfigFile('captcha')
            ->hasTranslations();
    }

    public function packageRegistered(): void
    {
        $this->app->scoped(CaptchaManager::class, function ($app) {
            return new CaptchaManager($app);
        });
    }
}
