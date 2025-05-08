<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
          // Register repositories
          $this->app->bind(
            \App\Repositories\Interfaces\PropertyRepositoryInterface::class,
            \App\Repositories\PropertyRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\UnitRepositoryInterface::class,
            \App\Repositories\UnitRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\TenantRepositoryInterface::class,
            \App\Repositories\TenantRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\LeaseRepositoryInterface::class,
            \App\Repositories\LeaseRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\PaymentRepositoryInterface::class,
            \App\Repositories\PaymentRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
