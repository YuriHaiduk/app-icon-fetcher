<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher;

use Illuminate\Support\ServiceProvider;
use Modules\AppIconFetcher\Providers\AppIconFetcherBindingServiceProvider;
use Modules\AppIconFetcher\Providers\AppIconFetcherDatabaseServiceProvider;
use Modules\AppIconFetcher\Providers\AppIconFetcherRouteServiceProvider;

class AppIconFetcherServiceProvider extends ServiceProvider
{
    /**
     * Register any module services.
     */
    public function register(): void
    {
        $this->app->register(AppIconFetcherRouteServiceProvider::class);
        $this->app->register(AppIconFetcherDatabaseServiceProvider::class);
        $this->app->register(AppIconFetcherBindingServiceProvider::class);
    }

    /**
     * Bootstrap any module services.
     */
    public function boot(): void
    {
        //
    }
}
