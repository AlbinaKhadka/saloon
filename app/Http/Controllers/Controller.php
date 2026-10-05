<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Saloon Project",
    version: "1.0.0",
    description: ""
)]

#[OA\Server(
    url: "https://saloon-rx72.onrender.com",
    description: "Live Production Server (Render)"
)]

#[OA\Server(
    url: "http://127.0.0.1:8000",
    description: "Local Development Server"
)]

#[OA\SecurityScheme(
    securityScheme: "sanctumCookie",
    type: "apiKey",
    in: "cookie",
    name: "laravel_session",
    description: "Laravel Sanctum session cookie authentication"
)]

abstract class Controller
{
    //
}
