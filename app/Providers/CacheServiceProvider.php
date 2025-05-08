<?php

namespace App\Providers;

use App\Domain\Property\Models\Property;
use App\Domain\Property\Models\Tenant;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

use Illuminate\Support\Facades\Cache;
class CacheServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
          // Listen for Property model events to automatically clear cache
          Property::created(function ($property) {
            $this->clearPropertyCache();
        });

        Property::updated(function ($property) {
            Cache::forget('property_' . $property->id);
            $this->clearPropertyCache();
        });

        Property::deleted(function ($property) {
            Cache::forget('property_' . $property->id);
            $this->clearPropertyCache();
        });

        // Listen for Tenant model events
        Tenant::created(function ($tenant) {
            $this->clearTenantCache();
        });

        Tenant::updated(function ($tenant) {
            Cache::forget('tenant_' . $tenant->id);
            $this->clearTenantCache();
        });

        Tenant::deleted(function ($tenant) {
            Cache::forget('tenant_' . $tenant->id);
            $this->clearTenantCache();
        });
    }

    /**
     * Clear property-related caches
     */
    private function clearPropertyCache()
    {
        $keys = Cache::get('property_cache_keys', []);

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        Cache::forever('property_cache_keys', []);
    }

    /**
     * Clear tenant-related caches
     */
    private function clearTenantCache()
    {
        $keys = Cache::get('tenant_cache_keys', []);

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        Cache::forever('tenant_cache_keys', []);
    }
}
