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
        //
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
