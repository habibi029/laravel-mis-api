<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Laravel\Passport\Passport;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->setupPassportTokens();
        $this->setupRateLimiters();
    }

    public function setupRateLimiters()
    {
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });
        return;
    }

    public function setupPassportTokens()
    {

        Passport::tokensCan([
            "admin_user" => 'Admin API User',
            "staff_user" => 'Staff API User',
        ]);

        Passport::tokensExpireIn(null);
        Passport::refreshTokensExpireIn(null);

        return;
    }
}
