<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Tests\Unit;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Modules\AppIconFetcher\Application\DTO\NormalizedAppInput;
use Modules\AppIconFetcher\Application\DTO\StoreIconResult;
use Modules\AppIconFetcher\Application\Enums\StoreType;
use Modules\AppIconFetcher\Application\Services\AppInputResolver;
use Modules\AppIconFetcher\Application\Services\FetchAppIconsService;
use Modules\AppIconFetcher\Infrastructure\Contracts\AppIconProviderInterface;
use Modules\AppIconFetcher\Infrastructure\Exceptions\InvalidAppInputException;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use RuntimeException;

final class FetchAppIconsServiceTest extends TestCase
{
    public function test_it_returns_both_apple_and_google_results_when_both_providers_find_icons(): void
    {
        $appleProvider = FakeAppIconProvider::found(StoreType::Apple, 'https://example.test/apple.png');
        $googleProvider = FakeAppIconProvider::found(StoreType::Google, 'https://example.test/google.png');

        $result = $this->service($appleProvider, $googleProvider)->fetch('com.u1.relax.minigame3');

        $this->assertSame('https://example.test/apple.png', $result->apple->iconUrl);
        $this->assertSame('https://example.test/google.png', $result->google->iconUrl);
        $this->assertSame(1, $appleProvider->fetchCalls);
        $this->assertSame(1, $googleProvider->fetchCalls);
    }

    public function test_it_returns_apple_found_and_google_not_found(): void
    {
        $result = $this->service(
            FakeAppIconProvider::found(StoreType::Apple, 'https://example.test/apple.png'),
            FakeAppIconProvider::notFound(StoreType::Google),
        )->fetch('com.u1.relax.minigame3');

        $this->assertTrue($result->apple->found);
        $this->assertFalse($result->google->found);
        $this->assertSame('Icon was not found in Google Play.', $result->google->message);
    }

    public function test_it_returns_google_found_and_apple_not_found(): void
    {
        $result = $this->service(
            FakeAppIconProvider::notFound(StoreType::Apple),
            FakeAppIconProvider::found(StoreType::Google, 'https://example.test/google.png'),
        )->fetch('com.u1.relax.minigame3');

        $this->assertFalse($result->apple->found);
        $this->assertSame('Icon was not found in Apple App Store.', $result->apple->message);
        $this->assertTrue($result->google->found);
    }

    public function test_it_does_not_treat_google_provider_failure_as_full_service_failure(): void
    {
        $appleProvider = FakeAppIconProvider::found(StoreType::Apple, 'https://example.test/apple.png');
        $googleProvider = FakeAppIconProvider::throwing(StoreType::Google);

        $result = $this->service($appleProvider, $googleProvider)->fetch('com.u1.relax.minigame3');

        $this->assertTrue($result->apple->found);
        $this->assertFalse($result->google->found);
        $this->assertSame('Google Play is temporarily unavailable.', $result->google->message);
        $this->assertSame(1, $appleProvider->fetchCalls);
        $this->assertSame(1, $googleProvider->fetchCalls);
    }

    public function test_it_returns_not_supported_for_google_when_input_only_has_apple_app_id(): void
    {
        $googleProvider = FakeAppIconProvider::found(StoreType::Google, 'https://example.test/google.png', supports: false);

        $result = $this->service(
            FakeAppIconProvider::found(StoreType::Apple, 'https://example.test/apple.png'),
            $googleProvider,
        )->fetch('https://apps.apple.com/ua/app/pubg-mobile/id1330123889?l=ru');

        $this->assertTrue($result->apple->found);
        $this->assertFalse($result->google->found);
        $this->assertSame('Google Play lookup requires a bundle/package id.', $result->google->message);
        $this->assertSame(0, $googleProvider->fetchCalls);
    }

    public function test_it_rethrows_invalid_app_input_exception_for_invalid_input(): void
    {
        $this->expectException(InvalidAppInputException::class);

        $this->service(
            FakeAppIconProvider::found(StoreType::Apple, 'https://example.test/apple.png'),
            FakeAppIconProvider::found(StoreType::Google, 'https://example.test/google.png'),
        )->fetch('hello world');
    }

    public function test_it_caches_successful_partial_result_and_reuses_cached_result(): void
    {
        $appleProvider = FakeAppIconProvider::found(StoreType::Apple, 'https://example.test/apple.png');
        $googleProvider = FakeAppIconProvider::notFound(StoreType::Google);
        $service = $this->service($appleProvider, $googleProvider);

        $firstResult = $service->fetch('  com.u1.relax.minigame3  ');
        $secondResult = $service->fetch('com.u1.relax.minigame3');

        $this->assertSame('https://example.test/apple.png', $firstResult->apple->iconUrl);
        $this->assertSame('https://example.test/apple.png', $secondResult->apple->iconUrl);
        $this->assertSame('Icon was not found in Google Play.', $secondResult->google->message);
        $this->assertSame(1, $appleProvider->fetchCalls);
        $this->assertSame(1, $googleProvider->fetchCalls);
    }

    private function service(FakeAppIconProvider $appleProvider, FakeAppIconProvider $googleProvider): FetchAppIconsService
    {
        return new FetchAppIconsService(
            inputResolver: new AppInputResolver,
            appleProvider: $appleProvider,
            googleProvider: $googleProvider,
            cache: new Repository(new ArrayStore),
            logger: new NullLogger,
        );
    }
}

final class FakeAppIconProvider implements AppIconProviderInterface
{
    public int $fetchCalls = 0;

    private function __construct(
        private readonly StoreType $store,
        private readonly StoreIconResult $result,
        private readonly bool $supports,
        private readonly bool $throws,
    ) {}

    public static function found(StoreType $store, string $iconUrl, bool $supports = true): self
    {
        return new self(
            store: $store,
            result: StoreIconResult::found($store, $iconUrl),
            supports: $supports,
            throws: false,
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
            throws: false,
        );
    }

    public static function throwing(StoreType $store): self
    {
        return new self(
            store: $store,
            result: StoreIconResult::failed($store, 'Unused result.'),
            supports: true,
            throws: true,
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

        if ($this->throws) {
            throw new RuntimeException('Provider failed unexpectedly.');
        }

        return $this->result;
    }
}
