<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Tests\Unit;

use Modules\AppIconFetcher\Application\Contracts\AppInputTypeResolverInterface;
use Modules\AppIconFetcher\Application\DTO\NormalizedAppInput;
use Modules\AppIconFetcher\Application\Enums\AppInputType;
use Modules\AppIconFetcher\Application\InputResolvers\AppleAppIdResolver;
use Modules\AppIconFetcher\Application\InputResolvers\AppleAppStoreUrlResolver;
use Modules\AppIconFetcher\Application\InputResolvers\BundleIdResolver;
use Modules\AppIconFetcher\Application\InputResolvers\GooglePlayUrlResolver;
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

    public function test_it_resolves_numeric_apple_app_id(): void
    {
        $result = $this->resolver()->resolve('6503284107');

        $this->assertSame('6503284107', $result->originalInput);
        $this->assertSame(AppInputType::AppleAppId, $result->type);
        $this->assertNull($result->bundleId);
        $this->assertSame('6503284107', $result->appleAppId);
    }

    public function test_it_resolves_id_prefixed_apple_app_id(): void
    {
        $result = $this->resolver()->resolve('id6503284107');

        $this->assertSame('id6503284107', $result->originalInput);
        $this->assertSame(AppInputType::AppleAppId, $result->type);
        $this->assertNull($result->bundleId);
        $this->assertSame('6503284107', $result->appleAppId);
    }

    public function test_it_resolves_uppercase_id_prefixed_apple_app_id(): void
    {
        $result = $this->resolver()->resolve('ID6503284107');

        $this->assertSame('ID6503284107', $result->originalInput);
        $this->assertSame(AppInputType::AppleAppId, $result->type);
        $this->assertNull($result->bundleId);
        $this->assertSame('6503284107', $result->appleAppId);
    }

    public function test_it_trims_id_prefixed_apple_app_id(): void
    {
        $result = $this->resolver()->resolve('  id6503284107  ');

        $this->assertSame('id6503284107', $result->originalInput);
        $this->assertSame(AppInputType::AppleAppId, $result->type);
        $this->assertNull($result->bundleId);
        $this->assertSame('6503284107', $result->appleAppId);
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

    public function test_it_rejects_id_without_numeric_apple_app_id(): void
    {
        $this->expectException(InvalidAppInputException::class);

        $this->resolver()->resolve('id');
    }

    public function test_it_rejects_id_prefixed_non_numeric_apple_app_id(): void
    {
        $this->expectException(InvalidAppInputException::class);

        $this->resolver()->resolve('idabc');
    }

    public function test_it_rejects_too_short_numeric_apple_app_id(): void
    {
        $this->expectException(InvalidAppInputException::class);

        $this->resolver()->resolve('123');
    }

    public function test_it_uses_the_first_resolver_that_supports_input(): void
    {
        $resolver = new AppInputResolver([
            new class implements AppInputTypeResolverInterface
            {
                public function supports(string $input): bool
                {
                    return $input === 'com.example.app';
                }

                public function resolve(string $input): NormalizedAppInput
                {
                    return new NormalizedAppInput(
                        originalInput: $input,
                        type: AppInputType::BundleId,
                        bundleId: 'first.resolver',
                        appleAppId: null,
                    );
                }
            },
            new class implements AppInputTypeResolverInterface
            {
                public function supports(string $input): bool
                {
                    return $input === 'com.example.app';
                }

                public function resolve(string $input): NormalizedAppInput
                {
                    return new NormalizedAppInput(
                        originalInput: $input,
                        type: AppInputType::BundleId,
                        bundleId: 'second.resolver',
                        appleAppId: null,
                    );
                }
            },
        ]);

        $result = $resolver->resolve('com.example.app');

        $this->assertSame('first.resolver', $result->bundleId);
    }

    private function resolver(): AppInputResolver
    {
        return new AppInputResolver([
            new GooglePlayUrlResolver,
            new AppleAppStoreUrlResolver,
            new AppleAppIdResolver,
            new BundleIdResolver,
        ]);
    }
}
