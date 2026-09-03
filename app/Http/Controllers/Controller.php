<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Salon SaaS API",
    version: "1.0.0",
    description: "API for salon booking, appointments, staff, and billing management"
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
