<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->statefulApi();

        $middleware->trustProxies(at: '*');

        // API requests never redirect to a (non-existent) login route
        $middleware->redirectGuestsTo(fn (Request $request) => null);

        $middleware->validateCsrfTokens(except: [
            'api/login',
            'api/logout',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) =>
                $request->is('api/*') || $request->is('user') || $request->expectsJson(),
        );

    })
    ->create();
