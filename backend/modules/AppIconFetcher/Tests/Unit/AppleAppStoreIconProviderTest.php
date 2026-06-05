<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Tests\Unit;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Modules\AppIconFetcher\Application\DTO\NormalizedAppInput;
use Modules\AppIconFetcher\Application\Enums\AppInputType;
use Modules\AppIconFetcher\Application\Enums\StoreType;
use Modules\AppIconFetcher\Infrastructure\Providers\AppleAppStoreIconProvider;
use Tests\TestCase;

final class AppleAppStoreIconProviderTest extends TestCase
{
    public function test_it_supports_input_with_apple_app_id(): void
    {
        $this->assertTrue($this->provider()->supports($this->appleInput()));
    }

    public function test_it_supports_input_with_bundle_id(): void
    {
        $this->assertTrue($this->provider()->supports($this->bundleInput()));
    }

    public function test_it_does_not_support_input_without_apple_app_id_and_bundle_id(): void
    {
        $input = new NormalizedAppInput(
            originalInput: 'unsupported',
            type: AppInputType::AppleAppStoreUrl,
            bundleId: null,
            appleAppId: null,
        );

        $this->assertFalse($this->provider()->supports($input));
    }

    public function test_it_fetches_icon_by_bundle_id_using_artwork_url_512(): void
    {
        Http::fake([
            'itunes.apple.com/*' => Http::response($this->lookupResponse([
                'artworkUrl512' => 'https://example.test/icon-512.png',
                'artworkUrl100' => 'https://example.test/icon-100.png',
            ])),
        ]);

        $result = $this->provider()->fetch($this->bundleInput());

        $this->assertSame(StoreType::Apple, $result->store);
        $this->assertTrue($result->found);
        $this->assertSame('https://example.test/icon-512.png', $result->iconUrl);
        $this->assertNull($result->message);
        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://itunes.apple.com/lookup?bundleId=com.u1.relax.minigame3');
    }

    public function test_it_fetches_icon_by_apple_app_id_using_artwork_url_512(): void
    {
        Http::fake([
            'itunes.apple.com/*' => Http::response($this->lookupResponse([
                'artworkUrl512' => 'https://example.test/apple-icon-512.png',
            ])),
        ]);

        $result = $this->provider()->fetch($this->appleInput());

        $this->assertTrue($result->found);
        $this->assertSame('https://example.test/apple-icon-512.png', $result->iconUrl);
        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://itunes.apple.com/lookup?id=1330123889');
    }

    public function test_it_falls_back_to_artwork_url_100_when_artwork_url_512_is_missing(): void
    {
        Http::fake([
            'itunes.apple.com/*' => Http::response($this->lookupResponse([
                'artworkUrl100' => 'https://example.test/icon-100.png',
            ])),
        ]);

        $result = $this->provider()->fetch($this->bundleInput());

        $this->assertTrue($result->found);
        $this->assertSame('https://example.test/icon-100.png', $result->iconUrl);
    }

    public function test_it_returns_not_found_when_result_count_is_zero(): void
    {
        Http::fake([
            'itunes.apple.com/*' => Http::response([
                'resultCount' => 0,
                'results' => [],
            ]),
        ]);

        $result = $this->provider()->fetch($this->bundleInput());

        $this->assertFalse($result->found);
        $this->assertNull($result->iconUrl);
        $this->assertSame('Icon was not found in Apple App Store.', $result->message);
    }

    public function test_it_returns_not_found_when_response_has_no_icon_url(): void
    {
        Http::fake([
            'itunes.apple.com/*' => Http::response($this->lookupResponse([
                'trackName' => 'PUBG Mobile',
            ])),
        ]);

        $result = $this->provider()->fetch($this->bundleInput());

        $this->assertFalse($result->found);
        $this->assertNull($result->iconUrl);
        $this->assertSame('Icon URL was not found in Apple App Store response.', $result->message);
    }

    public function test_it_returns_failed_result_on_http_server_failure_or_exception(): void
    {
        Http::fake([
            'itunes.apple.com/*' => Http::response(['error' => 'Server error'], 500),
        ]);

        $result = $this->provider()->fetch($this->bundleInput());

        $this->assertSame(StoreType::Apple, $result->store);
        $this->assertFalse($result->found);
        $this->assertNull($result->iconUrl);
        $this->assertSame('Apple App Store is temporarily unavailable.', $result->message);
    }

    private function provider(): AppleAppStoreIconProvider
    {
        return new AppleAppStoreIconProvider;
    }

    private function bundleInput(): NormalizedAppInput
    {
        return new NormalizedAppInput(
            originalInput: 'com.u1.relax.minigame3',
            type: AppInputType::BundleId,
            bundleId: 'com.u1.relax.minigame3',
            appleAppId: null,
        );
    }

    private function appleInput(): NormalizedAppInput
    {
        return new NormalizedAppInput(
            originalInput: 'https://apps.apple.com/ua/app/pubg-mobile/id1330123889?l=ru',
            type: AppInputType::AppleAppStoreUrl,
            bundleId: null,
            appleAppId: '1330123889',
        );
    }

    /**
     * @param  array<string, string>  $result
     * @return array{resultCount: int, results: array<int, array<string, string>>}
     */
    private function lookupResponse(array $result): array
    {
        return [
            'resultCount' => 1,
            'results' => [$result],
        ];
    }
}
