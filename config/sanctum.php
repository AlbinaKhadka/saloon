<?php

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Laravel\Sanctum\Http\Middleware\AuthenticateSession;
use Laravel\Sanctum\Sanctum;

return [

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    */

    'stateful' => array_values(array_unique(array_filter(array_map(function ($domain) {
        return preg_replace('#^https?://#', '', rtrim(trim($domain), '/'));
    }, explode(',', env(
        'SANCTUM_STATEFUL_DOMAINS',
        sprintf(
            '%s,%s',
            'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,127.0.0.1:3000,::1,lumina-salon-weld.vercel.app,lumina-salon-nepal.vercel.app,saloon-rx72.onrender.com',
            Sanctum::$currentRequestHostPlaceholder
        )
    )))))),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Guards
    |--------------------------------------------------------------------------
    */

    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    */

    'expiration' => null,

    /*
    |--------------------------------------------------------------------------
    | Token Prefix
    |--------------------------------------------------------------------------
    */

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Middleware
    |--------------------------------------------------------------------------
    */

    'middleware' => [
        'authenticate_session' => AuthenticateSession::class,
        'encrypt_cookies' => EncryptCookies::class,
        'validate_csrf_token' => ValidateCsrfToken::class,
    ],

];
