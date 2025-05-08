<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
         // Register services
         $this->app->bind(\App\Services\PropertyService::class, function ($app) {
            return new \App\Services\PropertyService(
                $app->make(\App\Repositories\Interfaces\PropertyRepositoryInterface::class)
            );
        });

        $this->app->bind(\App\Services\UnitService::class, function ($app) {
            return new \App\Services\UnitService(
                $app->make(\App\Repositories\Interfaces\UnitRepositoryInterface::class)
            );
        });

        $this->app->bind(\App\Services\TenantService::class, function ($app) {
            return new \App\Services\TenantService(
                $app->make(\App\Repositories\Interfaces\TenantRepositoryInterface::class)
            );
        });

        $this->app->bind(\App\Services\LeaseService::class, function ($app) {
            return new \App\Services\LeaseService(
                $app->make(\App\Repositories\Interfaces\LeaseRepositoryInterface::class),
                $app->make(\App\Repositories\Interfaces\UnitRepositoryInterface::class)
            );
        });

        $this->app->bind(\App\Services\PaymentService::class, function ($app) {
            return new \App\Services\PaymentService(
                $app->make(\App\Repositories\Interfaces\PaymentRepositoryInterface::class),
                $app->make(\App\Repositories\Interfaces\LeaseRepositoryInterface::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure the factory to look in the custom model directory
        Factory::guessModelNamesUsing(function (Factory $factory) {
            $modelName = $factory->modelName();

            // Check if the model exists in your custom namespace
            $customModelClass = 'App\\Domain\\Property\\Models\\' . class_basename($modelName);

            if (class_exists($customModelClass)) {
                return $customModelClass;
            }

            // Fall back to the original model name if not found
            return $modelName;
        });
    }
}
