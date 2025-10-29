<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Donor Module - Repository binding
        $this->app->bind(
            \App\Repositories\RepositoryInterface::class,
            \App\Modules\Donor\Repository\DonorRepository::class
        );

        // Donor Module - Service binding
        $this->app->bind(
            \App\Modules\Donor\Service\DonorService::class,
            function ($app) {
                return new \App\Modules\Donor\Service\DonorService(
                    $app->make(\App\Modules\Donor\Repository\DonorRepository::class)
                );
            }
        );

        // Beneficiary Module - Repository binding
        $this->app->bind(
            \App\Modules\Beneficiary\Repository\BeneficiaryRepository::class,
            function ($app) {
                return new \App\Modules\Beneficiary\Repository\BeneficiaryRepository(
                    $app->make(\App\Modules\Beneficiary\Domain\Beneficiary::class)
                );
            }
        );

        // Beneficiary Module - Service binding
        $this->app->bind(
            \App\Modules\Beneficiary\Service\BeneficiaryService::class,
            function ($app) {
                return new \App\Modules\Beneficiary\Service\BeneficiaryService(
                    $app->make(\App\Modules\Beneficiary\Repository\BeneficiaryRepository::class)
                );
            }
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
