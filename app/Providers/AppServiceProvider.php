<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        if (! app()->runningInConsole()) {
            $forwardedProto = request()->header('x-forwarded-proto');
            $cfVisitor = (string) request()->header('cf-visitor', '');

            if ($forwardedProto === 'https' || str_contains($cfVisitor, '"scheme":"https"')) {
                URL::forceScheme('https');
            }
        }
    }
}
