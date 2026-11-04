<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        // Vale para a troca de senha no perfil do painel.
        Password::defaults(fn () => Password::min(12)->mixedCase()->numbers()->symbols()->uncompromised());

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }
}
