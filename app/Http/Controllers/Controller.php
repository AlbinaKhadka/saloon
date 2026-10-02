<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Salon SaaS API",
    version: "1.0.0",
    description: "API for salon booking, appointments, staff, and billing management"
)]
#[OA\Server(
    url: "http://127.0.0.1:8000",
    description: "Local Development Server"
)]
#[OA\Server(
    url: "https://saloon-rx72.onrender.com",
    description: "Live Production Server (Render)"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "Enter your Sanctum token"
)]
abstract class Controller
{
    //
}
