<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Tests\Unit;

use Modules\AppIconFetcher\Application\Enums\AppInputType;
use Modules\AppIconFetcher\Application\Services\AppInputResolver;
use Modules\AppIconFetcher\Infrastructure\Exceptions\InvalidAppInputException;
use PHPUnit\Framework\TestCase;

final class AppInputResolverTest extends TestCase
{
    public function test_it_resolves_plain_bundle_id(): void
    {
        $result = $this->resolver()->resolve('com.u1.relax.minigame3');

        $this->assertSame('com.u1.relax.minigame3', $result->originalInput);
        $this->assertSame(AppInputType::BundleId, $result->type);
        $this->assertSame('com.u1.relax.minigame3', $result->bundleId);
        $this->assertNull($result->appleAppId);
    }

    public function test_it_trims_plain_bundle_id(): void
    {
        $result = $this->resolver()->resolve('  com.u1.relax.minigame3  ');

        $this->assertSame('com.u1.relax.minigame3', $result->originalInput);
        $this->assertSame(AppInputType::BundleId, $result->type);
        $this->assertSame('com.u1.relax.minigame3', $result->bundleId);
        $this->assertNull($result->appleAppId);
    }

    public function test_it_resolves_google_play_url_and_extracts_id(): void
    {
        $result = $this->resolver()->resolve('https://play.google.com/store/apps/details?id=com.u1.relax.minigame3');

        $this->assertSame(AppInputType::GooglePlayUrl, $result->type);
        $this->assertSame('com.u1.relax.minigame3', $result->bundleId);
        $this->assertNull($result->appleAppId);
    }

    public function test_it_resolves_google_play_url_with_extra_query_parameters(): void
    {
        $result = $this->resolver()->resolve('https://play.google.com/store/apps/details?id=com.u1.relax.minigame3&hl=uk&gl=UA');

        $this->assertSame(AppInputType::GooglePlayUrl, $result->type);
        $this->assertSame('com.u1.relax.minigame3', $result->bundleId);
        $this->assertNull($result->appleAppId);
    }

    public function test_it_resolves_apple_app_store_url_and_extracts_app_id(): void
    {
        $result = $this->resolver()->resolve('https://apps.apple.com/ua/app/pubg-mobile/id1330123889?l=ru');

        $this->assertSame(AppInputType::AppleAppStoreUrl, $result->type);
        $this->assertNull($result->bundleId);
        $this->assertSame('1330123889', $result->appleAppId);
    }

    public function test_it_rejects_empty_input(): void
    {
        $this->expectException(InvalidAppInputException::class);

        $this->resolver()->resolve('   ');
    }

    public function test_it_rejects_random_text(): void
    {
        $this->expectException(InvalidAppInputException::class);

        $this->resolver()->resolve('hello world');
    }

    public function test_it_rejects_unsupported_url(): void
    {
        $this->expectException(InvalidAppInputException::class);

        $this->resolver()->resolve('https://example.com/store/apps/details?id=com.u1.relax.minigame3');
    }

    public function test_it_rejects_invalid_google_play_url_without_id(): void
    {
        $this->expectException(InvalidAppInputException::class);

        $this->resolver()->resolve('https://play.google.com/store/apps/details?hl=uk');
    }

    public function test_it_rejects_invalid_bundle_id(): void
    {
        $this->expectException(InvalidAppInputException::class);

        $this->resolver()->resolve('com.u1..relax');
    }

    private function resolver(): AppInputResolver
    {
        return new AppInputResolver;
    }
}
