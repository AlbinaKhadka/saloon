<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\SecurityScheme(
    securityScheme: 'cookieAuth',
    type: 'apiKey',
    in: 'cookie',
    name: 'laravel_session',
    description: 'Laravel Sanctum session cookie authentication.'
)]
class SecuritySchemes
{
}
