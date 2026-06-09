<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Tests\Unit;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Modules\AppIconFetcher\Application\InputResolving\NormalizedAppInputDto;
use Modules\AppIconFetcher\Application\StoreIcons\AppIconProviderInterface;
use Modules\AppIconFetcher\Application\StoreIcons\StoreIconResultDto;
use Modules\AppIconFetcher\Application\StoreIcons\StoreType;
use Modules\AppIconFetcher\Application\InputResolving\Resolvers\AppleAppIdResolver;
use Modules\AppIconFetcher\Application\InputResolving\Resolvers\AppleAppStoreUrlResolver;
use Modules\AppIconFetcher\Application\InputResolving\Resolvers\BundleIdResolver;
use Modules\AppIconFetcher\Application\InputResolving\Resolvers\GooglePlayUrlResolver;
use Modules\AppIconFetcher\Application\InputResolving\AppInputResolver;
use Modules\AppIconFetcher\Application\UseCases\FetchAppIcons\FetchAppIconsService;
use Modules\AppIconFetcher\Infrastructure\Cache\FetchAppIconsCache;
use Modules\AppIconFetcher\Infrastructure\Exceptions\InvalidAppInputException;
use PHPUnit\Framework\TestCase;

final class FetchAppIconsServiceTest extends TestCase
{
    public function test_it_returns_both_apple_and_google_results_when_both_clients_find_icons(): void
    {
        $appleProvider = FakeAppIconProvider::found(StoreType::Apple, 'https://example.test/apple.png');
        $googleProvider = FakeAppIconProvider::found(StoreType::Google, 'https://example.test/google.png');

        $result = $this->service([$appleProvider, $googleProvider])->fetch('com.u1.relax.minigame3');

        $this->assertSame('https://example.test/apple.png', $result->resultFor(StoreType::Apple)->iconUrl);
        $this->assertSame('https://example.test/google.png', $result->resultFor(StoreType::Google)->iconUrl);
        $this->assertSame(1, $appleProvider->fetchCalls);
        $this->assertSame(1, $googleProvider->fetchCalls);
    }

    public function test_it_returns_apple_found_and_google_not_found(): void
    {
        $result = $this->service(
            [
                FakeAppIconProvider::found(StoreType::Apple, 'https://example.test/apple.png'),
                FakeAppIconProvider::notFound(StoreType::Google),
            ],
        )->fetch('com.u1.relax.minigame3');

        $this->assertTrue($result->resultFor(StoreType::Apple)->found);
        $this->assertFalse($result->resultFor(StoreType::Google)->found);
        $this->assertSame('Icon was not found in Google Play.', $result->resultFor(StoreType::Google)->message);
    }

    public function test_it_returns_google_found_and_apple_not_found(): void
    {
        $result = $this->service(
            [
                FakeAppIconProvider::notFound(StoreType::Apple),
                FakeAppIconProvider::found(StoreType::Google, 'https://example.test/google.png'),
            ],
        )->fetch('com.u1.relax.minigame3');

        $this->assertFalse($result->resultFor(StoreType::Apple)->found);
        $this->assertSame('Icon was not found in Apple App Store.', $result->resultFor(StoreType::Apple)->message);
        $this->assertTrue($result->resultFor(StoreType::Google)->found);
    }

    public function test_it_does_not_treat_google_client_failure_as_full_service_failure(): void
    {
        $appleProvider = FakeAppIconProvider::found(StoreType::Apple, 'https://example.test/apple.png');
        $googleProvider = FakeAppIconProvider::failed(StoreType::Google);

        $result = $this->service([$appleProvider, $googleProvider])->fetch('com.u1.relax.minigame3');

        $this->assertTrue($result->resultFor(StoreType::Apple)->found);
        $this->assertFalse($result->resultFor(StoreType::Google)->found);
        $this->assertSame('Google Play is temporarily unavailable.', $result->resultFor(StoreType::Google)->message);
        $this->assertSame(1, $appleProvider->fetchCalls);
        $this->assertSame(1, $googleProvider->fetchCalls);
    }

    public function test_it_returns_not_supported_for_google_when_input_only_has_apple_app_id(): void
    {
        $googleProvider = FakeAppIconProvider::notSupported(StoreType::Google);

        $result = $this->service(
            [
                FakeAppIconProvider::found(StoreType::Apple, 'https://example.test/apple.png'),
                $googleProvider,
            ],
        )->fetch('https://apps.apple.com/ua/app/pubg-mobile/id1330123889?l=ru');

        $this->assertTrue($result->resultFor(StoreType::Apple)->found);
        $this->assertFalse($result->resultFor(StoreType::Google)->found);
        $this->assertSame('Google Play lookup requires a bundle/package id.', $result->resultFor(StoreType::Google)->message);
        $this->assertSame(1, $googleProvider->fetchCalls);
    }

    public function test_it_rethrows_invalid_app_input_exception_for_invalid_input(): void
    {
        $this->expectException(InvalidAppInputException::class);

        $this->service(
            [
                FakeAppIconProvider::found(StoreType::Apple, 'https://example.test/apple.png'),
                FakeAppIconProvider::found(StoreType::Google, 'https://example.test/google.png'),
            ],
        )->fetch('hello world');
    }

    public function test_it_caches_successful_partial_result_and_reuses_cached_result(): void
    {
        $appleProvider = FakeAppIconProvider::found(StoreType::Apple, 'https://example.test/apple.png');
        $googleProvider = FakeAppIconProvider::notFound(StoreType::Google);
        $service = $this->service([$appleProvider, $googleProvider]);

        $firstResult = $service->fetch('  com.u1.relax.minigame3  ');
        $secondResult = $service->fetch('com.u1.relax.minigame3');

        $this->assertSame('https://example.test/apple.png', $firstResult->resultFor(StoreType::Apple)->iconUrl);
        $this->assertSame('https://example.test/apple.png', $secondResult->resultFor(StoreType::Apple)->iconUrl);
        $this->assertSame('Icon was not found in Google Play.', $secondResult->resultFor(StoreType::Google)->message);
        $this->assertSame(1, $appleProvider->fetchCalls);
        $this->assertSame(1, $googleProvider->fetchCalls);
    }

    /**
     * @param  iterable<AppIconProviderInterface>  $providers
     */
    private function service(iterable $providers): FetchAppIconsService
    {
        return new FetchAppIconsService(
            inputResolver: new AppInputResolver([
                new GooglePlayUrlResolver,
                new AppleAppStoreUrlResolver,
                new AppleAppIdResolver,
                new BundleIdResolver,
            ]),
            providers: $providers,
            cache: new FetchAppIconsCache(new Repository(new ArrayStore)),
        );
    }
}

final class FakeAppIconProvider implements AppIconProviderInterface
{
    public int $fetchCalls = 0;

    private function __construct(
        private readonly StoreType $store,
        private readonly StoreIconResultDto $result,
    ) {}

    public static function found(StoreType $store, string $iconUrl): self
    {
        return new self(
            store: $store,
            result: StoreIconResultDto::found($store, $iconUrl),
        );
    }

    public static function notFound(StoreType $store): self
    {
        return new self(
            store: $store,
            result: StoreIconResultDto::notFound($store, match ($store) {
                StoreType::Apple => 'Icon was not found in Apple App Store.',
                StoreType::Google => 'Icon was not found in Google Play.',
            }),
        );
    }

    public static function notSupported(StoreType $store): self
    {
        return new self(
            store: $store,
            result: StoreIconResultDto::notSupported($store, match ($store) {
                StoreType::Apple => 'Apple App Store lookup requires an Apple app id or bundle/package id.',
                StoreType::Google => 'Google Play lookup requires a bundle/package id.',
            }),
        );
    }

    public static function failed(StoreType $store): self
    {
        return new self(
            store: $store,
            result: StoreIconResultDto::failed($store, match ($store) {
                StoreType::Apple => 'Apple App Store is temporarily unavailable.',
                StoreType::Google => 'Google Play is temporarily unavailable.',
            }),
        );
    }

    public function store(): StoreType
    {
        return $this->store;
    }

    public function fetch(NormalizedAppInputDto $input): StoreIconResultDto
    {
        $this->fetchCalls++;

        return $this->result;
    }
}
