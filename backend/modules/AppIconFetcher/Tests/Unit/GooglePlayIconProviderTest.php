<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Tests\Unit;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Modules\AppIconFetcher\Application\DTO\NormalizedAppInput;
use Modules\AppIconFetcher\Application\Enums\AppInputType;
use Modules\AppIconFetcher\Application\Enums\StoreType;
use Modules\AppIconFetcher\Infrastructure\Providers\GooglePlayIconProvider;
use Tests\TestCase;

final class GooglePlayIconProviderTest extends TestCase
{
    public function test_it_supports_input_with_bundle_id(): void
    {
        $this->assertTrue($this->provider()->supports($this->bundleInput()));
    }

    public function test_it_does_not_support_input_without_bundle_id(): void
    {
        $this->assertFalse($this->provider()->supports($this->appleOnlyInput()));
    }

    public function test_it_returns_not_supported_when_fetch_is_called_without_bundle_id(): void
    {
        Http::fake();

        $result = $this->provider()->fetch($this->appleOnlyInput());

        $this->assertSame(StoreType::Google, $result->store);
        $this->assertFalse($result->found);
        $this->assertNull($result->iconUrl);
        $this->assertSame('Google Play lookup requires a bundle/package id.', $result->message);
        Http::assertNothingSent();
    }

    public function test_it_fetches_icon_from_meta_property_og_image(): void
    {
        Http::fake([
            'play.google.com/*' => Http::response('<html><head><meta property="og:image" content="https://example.test/google-icon.png"></head></html>'),
        ]);

        $result = $this->provider()->fetch($this->bundleInput());

        $this->assertSame(StoreType::Google, $result->store);
        $this->assertTrue($result->found);
        $this->assertSame('https://example.test/google-icon.png', $result->iconUrl);
        $this->assertNull($result->message);
        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://play.google.com/store/apps/details?id=com.u1.relax.minigame3&hl=en&gl=US'
            && $request->hasHeader('User-Agent'));
    }

    public function test_it_fetches_icon_when_meta_attributes_are_reversed(): void
    {
        Http::fake([
            'play.google.com/*' => Http::response('<meta content="https://example.test/reversed-icon.png" property="og:image">'),
        ]);

        $result = $this->provider()->fetch($this->bundleInput());

        $this->assertTrue($result->found);
        $this->assertSame('https://example.test/reversed-icon.png', $result->iconUrl);
    }

    public function test_it_fetches_icon_from_twitter_image_fallback(): void
    {
        Http::fake([
            'play.google.com/*' => Http::response('<meta name="twitter:image" content="https://example.test/twitter-icon.png">'),
        ]);

        $result = $this->provider()->fetch($this->bundleInput());

        $this->assertTrue($result->found);
        $this->assertSame('https://example.test/twitter-icon.png', $result->iconUrl);
    }

    public function test_it_decodes_html_entities_in_the_icon_url(): void
    {
        Http::fake([
            'play.google.com/*' => Http::response('<meta property="og:image" content="https://example.test/icon.png?size=512&amp;quality=90">'),
        ]);

        $result = $this->provider()->fetch($this->bundleInput());

        $this->assertTrue($result->found);
        $this->assertSame('https://example.test/icon.png?size=512&quality=90', $result->iconUrl);
    }

    public function test_it_returns_not_found_on_404(): void
    {
        Http::fake([
            'play.google.com/*' => Http::response('Not found', 404),
        ]);

        $result = $this->provider()->fetch($this->bundleInput());

        $this->assertFalse($result->found);
        $this->assertNull($result->iconUrl);
        $this->assertSame('Icon was not found in Google Play.', $result->message);
    }

    public function test_it_returns_not_found_when_html_has_no_icon_url(): void
    {
        Http::fake([
            'play.google.com/*' => Http::response('<html><head><title>Google Play</title></head></html>'),
        ]);

        $result = $this->provider()->fetch($this->bundleInput());

        $this->assertFalse($result->found);
        $this->assertNull($result->iconUrl);
        $this->assertSame('Icon URL was not found in Google Play response.', $result->message);
    }

    public function test_it_returns_failed_result_on_http_server_failure_or_exception(): void
    {
        Http::fake([
            'play.google.com/*' => Http::response('Server error', 500),
        ]);

        $result = $this->provider()->fetch($this->bundleInput());

        $this->assertSame(StoreType::Google, $result->store);
        $this->assertFalse($result->found);
        $this->assertNull($result->iconUrl);
        $this->assertSame('Google Play is temporarily unavailable.', $result->message);
    }

    private function provider(): GooglePlayIconProvider
    {
        return new GooglePlayIconProvider;
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

    private function appleOnlyInput(): NormalizedAppInput
    {
        return new NormalizedAppInput(
            originalInput: 'https://apps.apple.com/ua/app/pubg-mobile/id1330123889?l=ru',
            type: AppInputType::AppleAppStoreUrl,
            bundleId: null,
            appleAppId: '1330123889',
        );
    }
}
