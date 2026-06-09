<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Providers;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Modules\AppIconFetcher\Application\InputResolving\Resolvers\AppleAppIdResolver;
use Modules\AppIconFetcher\Application\InputResolving\Resolvers\AppleAppStoreUrlResolver;
use Modules\AppIconFetcher\Application\InputResolving\Resolvers\BundleIdResolver;
use Modules\AppIconFetcher\Application\InputResolving\Resolvers\GooglePlayUrlResolver;
use Modules\AppIconFetcher\Application\InputResolving\AppInputResolver;
use Modules\AppIconFetcher\Application\UseCases\FetchAppIcons\FetchAppIconsService;
use Modules\AppIconFetcher\Infrastructure\Cache\FetchAppIconsCache;
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
        $this->app->singleton(FetchAppIconsCache::class, function (Application $app): FetchAppIconsCache {
            return new FetchAppIconsCache($app->make(CacheRepository::class));
        });

        $this->app->singleton(AppInputResolver::class, function (): AppInputResolver {
            return new AppInputResolver([
                new GooglePlayUrlResolver,
                new AppleAppStoreUrlResolver,
                new AppleAppIdResolver,
                new BundleIdResolver,
            ]);
        });

        $this->app->singleton(FetchAppIconsService::class, function (Application $app): FetchAppIconsService {
            return new FetchAppIconsService(
                inputResolver: $app->make(AppInputResolver::class),
                appleClient: $app->make(AppleAppStoreIconClient::class),
                googleClient: $app->make(GooglePlayIconClient::class),
                cache: $app->make(FetchAppIconsCache::class),
            );
        });
    }
}
