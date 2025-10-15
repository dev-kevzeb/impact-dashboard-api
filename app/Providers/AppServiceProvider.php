<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Donor Module - Repository binding
        $this->app->bind(
            \App\Modules\Donor\Repository\DonorRepositoryContract::class,
            \App\Modules\Donor\Repository\DonorRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
