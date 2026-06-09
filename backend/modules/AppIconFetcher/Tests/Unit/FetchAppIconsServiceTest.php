<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Tests\Unit;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Modules\AppIconFetcher\Application\DTO\NormalizedAppInput;
use Modules\AppIconFetcher\Application\DTO\StoreIconResult;
use Modules\AppIconFetcher\Application\Enums\StoreType;
use Modules\AppIconFetcher\Application\InputResolvers\AppleAppIdResolver;
use Modules\AppIconFetcher\Application\InputResolvers\AppleAppStoreUrlResolver;
use Modules\AppIconFetcher\Application\InputResolvers\BundleIdResolver;
use Modules\AppIconFetcher\Application\InputResolvers\GooglePlayUrlResolver;
use Modules\AppIconFetcher\Application\Services\AppInputResolver;
use Modules\AppIconFetcher\Application\Services\FetchAppIconsService;
use Modules\AppIconFetcher\Infrastructure\Cache\FetchAppIconsCache;
use Modules\AppIconFetcher\Infrastructure\Contracts\AppIconClientInterface;
use Modules\AppIconFetcher\Infrastructure\Exceptions\InvalidAppInputException;
use PHPUnit\Framework\TestCase;

final class FetchAppIconsServiceTest extends TestCase
{
    public function test_it_returns_both_apple_and_google_results_when_both_clients_find_icons(): void
    {
        $appleClient = FakeAppIconClient::found(StoreType::Apple, 'https://example.test/apple.png');
        $googleClient = FakeAppIconClient::found(StoreType::Google, 'https://example.test/google.png');

        $result = $this->service($appleClient, $googleClient)->fetch('com.u1.relax.minigame3');

        $this->assertSame('https://example.test/apple.png', $result->apple->iconUrl);
        $this->assertSame('https://example.test/google.png', $result->google->iconUrl);
        $this->assertSame(1, $appleClient->fetchCalls);
        $this->assertSame(1, $googleClient->fetchCalls);
    }

    public function test_it_returns_apple_found_and_google_not_found(): void
    {
        $result = $this->service(
            FakeAppIconClient::found(StoreType::Apple, 'https://example.test/apple.png'),
            FakeAppIconClient::notFound(StoreType::Google),
        )->fetch('com.u1.relax.minigame3');

        $this->assertTrue($result->apple->found);
        $this->assertFalse($result->google->found);
        $this->assertSame('Icon was not found in Google Play.', $result->google->message);
    }

    public function test_it_returns_google_found_and_apple_not_found(): void
    {
        $result = $this->service(
            FakeAppIconClient::notFound(StoreType::Apple),
            FakeAppIconClient::found(StoreType::Google, 'https://example.test/google.png'),
        )->fetch('com.u1.relax.minigame3');

        $this->assertFalse($result->apple->found);
        $this->assertSame('Icon was not found in Apple App Store.', $result->apple->message);
        $this->assertTrue($result->google->found);
    }

    public function test_it_does_not_treat_google_client_failure_as_full_service_failure(): void
    {
        $appleClient = FakeAppIconClient::found(StoreType::Apple, 'https://example.test/apple.png');
        $googleClient = FakeAppIconClient::failed(StoreType::Google);

        $result = $this->service($appleClient, $googleClient)->fetch('com.u1.relax.minigame3');

        $this->assertTrue($result->apple->found);
        $this->assertFalse($result->google->found);
        $this->assertSame('Google Play is temporarily unavailable.', $result->google->message);
        $this->assertSame(1, $appleClient->fetchCalls);
        $this->assertSame(1, $googleClient->fetchCalls);
    }

    public function test_it_returns_not_supported_for_google_when_input_only_has_apple_app_id(): void
    {
        $googleClient = FakeAppIconClient::found(StoreType::Google, 'https://example.test/google.png', supports: false);

        $result = $this->service(
            FakeAppIconClient::found(StoreType::Apple, 'https://example.test/apple.png'),
            $googleClient,
        )->fetch('https://apps.apple.com/ua/app/pubg-mobile/id1330123889?l=ru');

        $this->assertTrue($result->apple->found);
        $this->assertFalse($result->google->found);
        $this->assertSame('Google Play lookup requires a bundle/package id.', $result->google->message);
        $this->assertSame(0, $googleClient->fetchCalls);
    }

    public function test_it_rethrows_invalid_app_input_exception_for_invalid_input(): void
    {
        $this->expectException(InvalidAppInputException::class);

        $this->service(
            FakeAppIconClient::found(StoreType::Apple, 'https://example.test/apple.png'),
            FakeAppIconClient::found(StoreType::Google, 'https://example.test/google.png'),
        )->fetch('hello world');
    }

    public function test_it_caches_successful_partial_result_and_reuses_cached_result(): void
    {
        $appleClient = FakeAppIconClient::found(StoreType::Apple, 'https://example.test/apple.png');
        $googleClient = FakeAppIconClient::notFound(StoreType::Google);
        $service = $this->service($appleClient, $googleClient);

        $firstResult = $service->fetch('  com.u1.relax.minigame3  ');
        $secondResult = $service->fetch('com.u1.relax.minigame3');

        $this->assertSame('https://example.test/apple.png', $firstResult->apple->iconUrl);
        $this->assertSame('https://example.test/apple.png', $secondResult->apple->iconUrl);
        $this->assertSame('Icon was not found in Google Play.', $secondResult->google->message);
        $this->assertSame(1, $appleClient->fetchCalls);
        $this->assertSame(1, $googleClient->fetchCalls);
    }

    private function service(FakeAppIconClient $appleClient, FakeAppIconClient $googleClient): FetchAppIconsService
    {
        return new FetchAppIconsService(
            inputResolver: new AppInputResolver([
                new GooglePlayUrlResolver,
                new AppleAppStoreUrlResolver,
                new AppleAppIdResolver,
                new BundleIdResolver,
            ]),
            appleClient: $appleClient,
            googleClient: $googleClient,
            cache: new FetchAppIconsCache(new Repository(new ArrayStore)),
        );
    }
}

final class FakeAppIconClient implements AppIconClientInterface
{
    public int $fetchCalls = 0;

    private function __construct(
        private readonly StoreType $store,
        private readonly StoreIconResult $result,
        private readonly bool $supports,
    ) {}

    public static function found(StoreType $store, string $iconUrl, bool $supports = true): self
    {
        return new self(
            store: $store,
            result: StoreIconResult::found($store, $iconUrl),
            supports: $supports,
        );
    }

    public static function notFound(StoreType $store, bool $supports = true): self
    {
        return new self(
            store: $store,
            result: StoreIconResult::notFound($store, match ($store) {
                StoreType::Apple => 'Icon was not found in Apple App Store.',
                StoreType::Google => 'Icon was not found in Google Play.',
            }),
            supports: $supports,
        );
    }

    public static function failed(StoreType $store): self
    {
        return new self(
            store: $store,
            result: StoreIconResult::failed($store, match ($store) {
                StoreType::Apple => 'Apple App Store is temporarily unavailable.',
                StoreType::Google => 'Google Play is temporarily unavailable.',
            }),
            supports: true,
        );
    }

    public function store(): StoreType
    {
        return $this->store;
    }

    public function supports(NormalizedAppInput $input): bool
    {
        return $this->supports;
    }

    public function fetch(NormalizedAppInput $input): StoreIconResult
    {
        $this->fetchCalls++;

        return $this->result;
    }
}
