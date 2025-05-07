<?php

use App\Http\Middleware\APIUser;
use App\Http\Middleware\APIGuest;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Laravel\Passport\Http\Middleware\CheckScopes;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Passport\Http\Middleware\CheckForAnyScope;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::prefix('api')
                ->middleware('api')
                ->group(base_path('routes/api/admin.php'));
            Route::prefix('api')
                ->middleware('api')
                ->group(base_path('routes/api/staff.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'guest.api' => APIGuest::class,
            'user.api' => APIUser::class,
            'scopes' => CheckScopes::class,
            'scope' => CheckForAnyScope::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
