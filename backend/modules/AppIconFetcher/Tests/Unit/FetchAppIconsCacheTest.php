<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Tests\Unit;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Modules\AppIconFetcher\Application\UseCases\FetchAppIcons\FetchAppIconsResultDto;
use Modules\AppIconFetcher\Application\InputResolving\NormalizedAppInputDto;
use Modules\AppIconFetcher\Application\StoreIcons\StoreIconResultDto;
use Modules\AppIconFetcher\Application\InputResolving\AppInputType;
use Modules\AppIconFetcher\Application\StoreIcons\StoreType;
use Modules\AppIconFetcher\Infrastructure\Cache\FetchAppIconsCache;
use PHPUnit\Framework\TestCase;

final class FetchAppIconsCacheTest extends TestCase
{
    public function test_it_restores_cached_fetch_app_icons_result(): void
    {
        $cache = new FetchAppIconsCache(new Repository(new ArrayStore));
        $input = new NormalizedAppInputDto(
            originalInput: '  com.u1.relax.minigame3  ',
            type: AppInputType::BundleId,
            bundleId: 'com.u1.relax.minigame3',
            appleAppId: null,
        );
        $result = new FetchAppIconsResultDto(
            input: $input,
            apple: StoreIconResultDto::found(StoreType::Apple, 'https://example.test/apple.png'),
            google: StoreIconResultDto::notFound(StoreType::Google, 'Icon was not found in Google Play.'),
        );

        $cache->put($input, $result);

        $cachedResult = $cache->get(new NormalizedAppInputDto(
            originalInput: 'com.u1.relax.minigame3',
            type: AppInputType::BundleId,
            bundleId: 'com.u1.relax.minigame3',
            appleAppId: null,
        ));

        $this->assertInstanceOf(FetchAppIconsResultDto::class, $cachedResult);
        $this->assertSame('  com.u1.relax.minigame3  ', $cachedResult->input->originalInput);
        $this->assertSame(AppInputType::BundleId, $cachedResult->input->type);
        $this->assertSame('com.u1.relax.minigame3', $cachedResult->input->bundleId);
        $this->assertNull($cachedResult->input->appleAppId);
        $this->assertSame(StoreType::Apple, $cachedResult->apple->store);
        $this->assertTrue($cachedResult->apple->found);
        $this->assertSame('https://example.test/apple.png', $cachedResult->apple->iconUrl);
        $this->assertNull($cachedResult->apple->message);
        $this->assertSame(StoreType::Google, $cachedResult->google->store);
        $this->assertFalse($cachedResult->google->found);
        $this->assertNull($cachedResult->google->iconUrl);
        $this->assertSame('Icon was not found in Google Play.', $cachedResult->google->message);
    }

    public function test_it_returns_null_when_cache_entry_does_not_exist(): void
    {
        $cache = new FetchAppIconsCache(new Repository(new ArrayStore));

        $result = $cache->get(new NormalizedAppInputDto(
            originalInput: 'com.u1.relax.minigame3',
            type: AppInputType::BundleId,
            bundleId: 'com.u1.relax.minigame3',
            appleAppId: null,
        ));

        $this->assertNull($result);
    }
}
