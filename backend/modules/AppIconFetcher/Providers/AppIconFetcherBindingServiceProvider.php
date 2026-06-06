<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Providers;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Modules\AppIconFetcher\Application\Services\AppInputResolver;
use Modules\AppIconFetcher\Application\Services\FetchAppIconsService;
use Modules\AppIconFetcher\Infrastructure\Clients\AppleAppStoreIconClient;
use Modules\AppIconFetcher\Infrastructure\Clients\GooglePlayIconClient;

class AppIconFetcherBindingServiceProvider extends ServiceProvider
{
    /**
     * Register module bindings.
     */
    public function register(): void
    {
        $this->app->singleton(AppleAppStoreIconClient::class);
        $this->app->singleton(GooglePlayIconClient::class);

        $this->app->singleton(FetchAppIconsService::class, function (Application $app): FetchAppIconsService {
            return new FetchAppIconsService(
                inputResolver: $app->make(AppInputResolver::class),
                appleClient: $app->make(AppleAppStoreIconClient::class),
                googleClient: $app->make(GooglePlayIconClient::class),
                cache: $app->make(CacheRepository::class),
            );
        });
    }
}
