<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Providers;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Modules\AppIconFetcher\Application\Services\AppInputResolver;
use Modules\AppIconFetcher\Application\Services\FetchAppIconsService;
use Modules\AppIconFetcher\Infrastructure\Providers\AppleAppStoreIconProvider;
use Modules\AppIconFetcher\Infrastructure\Providers\GooglePlayIconProvider;
use Psr\Log\LoggerInterface;

class AppIconFetcherBindingServiceProvider extends ServiceProvider
{
    /**
     * Register module bindings.
     */
    public function register(): void
    {
        $this->app->singleton(AppleAppStoreIconProvider::class);
        $this->app->singleton(GooglePlayIconProvider::class);

        $this->app->singleton(FetchAppIconsService::class, function (Application $app): FetchAppIconsService {
            return new FetchAppIconsService(
                inputResolver: $app->make(AppInputResolver::class),
                appleProvider: $app->make(AppleAppStoreIconProvider::class),
                googleProvider: $app->make(GooglePlayIconProvider::class),
                cache: $app->make(CacheRepository::class),
                logger: $app->make(LoggerInterface::class),
            );
        });
    }
}
