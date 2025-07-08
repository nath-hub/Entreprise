<?php

namespace App\Providers;

use App\Models\Entreprise;
use App\Policies\EntreprisePolicy;
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
        //
    }

    protected $policies = [
        // ...
        Entreprise::class => EntreprisePolicy::class,
    ];
}
