<?php

// config for MrJin/Captcha
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
