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

final class AppIconFetcherBindingServiceProvider extends ServiceProvider
{
    private const AppIconProvidersTag = 'app-icon-fetcher.providers';

    public function register(): void
    {
        $this->app->singleton(AppleAppStoreIconClient::class);
        $this->app->singleton(GooglePlayIconClient::class);

        $this->app->tag([
            AppleAppStoreIconClient::class,
            GooglePlayIconClient::class,
        ], self::AppIconProvidersTag);

        $this->app->singleton(FetchAppIconsCache::class, function (Application $app): FetchAppIconsCache {
            return new FetchAppIconsCache($app->make(CacheRepository::class));
        });

        $this->app->singleton(AppInputResolver::class, function (Application $app): AppInputResolver {
            return new AppInputResolver([
                $app->make(GooglePlayUrlResolver::class),
                $app->make(AppleAppStoreUrlResolver::class),
                $app->make(AppleAppIdResolver::class),
                $app->make(BundleIdResolver::class),
            ]);
        });

        $this->app->singleton(FetchAppIconsService::class, function (Application $app): FetchAppIconsService {
            return new FetchAppIconsService(
                inputResolver: $app->make(AppInputResolver::class),
                providers: $app->tagged(self::AppIconProvidersTag),
                cache: $app->make(FetchAppIconsCache::class),
            );
        });
    }
}
