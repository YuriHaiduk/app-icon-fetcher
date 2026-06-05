<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class FetchAppIconsApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_missing_input_returns_422(): void
    {
        $response = $this->getJson('/api/v1/app-icons');

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['input']);
    }

    public function test_invalid_input_returns_422(): void
    {
        $response = $this->getJson('/api/v1/app-icons?input=hello%20world');

        $response->assertUnprocessable()
            ->assertJsonPath('errors.input.0', 'Please provide a valid bundle ID or supported app store URL.');
    }

    public function test_valid_bundle_id_returns_http_200(): void
    {
        $this->fakeStoreResponses(
            appleIconUrl: 'https://example.test/apple.png',
            googleHtml: '<meta property="og:image" content="https://example.test/google.png">',
        );

        $response = $this->getJson('/api/v1/app-icons?input=com.u1.relax.minigame3');

        $response->assertOk()
            ->assertJsonPath('data.input.type', 'bundle_id')
            ->assertJsonPath('data.input.bundle_id', 'com.u1.relax.minigame3')
            ->assertJsonPath('data.icons.apple.icon_url', 'https://example.test/apple.png')
            ->assertJsonPath('data.icons.google.icon_url', 'https://example.test/google.png');
    }

    public function test_response_has_stable_json_structure(): void
    {
        $this->fakeStoreResponses(
            appleIconUrl: 'https://example.test/apple.png',
            googleHtml: '<meta property="og:image" content="https://example.test/google.png">',
        );

        $response = $this->getJson('/api/v1/app-icons?input=com.u1.relax.minigame3');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'input' => [
                        'original',
                        'type',
                        'bundle_id',
                        'apple_app_id',
                    ],
                    'icons' => [
                        'apple' => [
                            'found',
                            'icon_url',
                            'message',
                        ],
                        'google' => [
                            'found',
                            'icon_url',
                            'message',
                        ],
                    ],
                ],
            ]);
    }

    public function test_partial_result_still_returns_http_200(): void
    {
        $this->fakeStoreResponses(
            appleIconUrl: 'https://example.test/apple.png',
            googleHtml: '<html><head><title>Google Play</title></head></html>',
        );

        $response = $this->getJson('/api/v1/app-icons?input=com.u1.relax.minigame3');

        $response->assertOk()
            ->assertJsonPath('data.icons.apple.found', true)
            ->assertJsonPath('data.icons.google.found', false)
            ->assertJsonPath('data.icons.google.message', 'Icon URL was not found in Google Play response.');
    }

    public function test_apple_app_store_url_input_returns_http_200_and_includes_apple_app_id(): void
    {
        Http::fake([
            'itunes.apple.com/*' => Http::response([
                'resultCount' => 1,
                'results' => [
                    ['artworkUrl512' => 'https://example.test/apple.png'],
                ],
            ]),
        ]);

        $response = $this->getJson('/api/v1/app-icons?input='.urlencode('https://apps.apple.com/ua/app/pubg-mobile/id1330123889?l=ru'));

        $response->assertOk()
            ->assertJsonPath('data.input.type', 'apple_app_store_url')
            ->assertJsonPath('data.input.bundle_id', null)
            ->assertJsonPath('data.input.apple_app_id', '1330123889')
            ->assertJsonPath('data.icons.apple.found', true)
            ->assertJsonPath('data.icons.google.found', false)
            ->assertJsonPath('data.icons.google.message', 'Google Play lookup requires a bundle/package id.');
    }

    public function test_google_play_url_input_returns_http_200_and_includes_bundle_id(): void
    {
        $this->fakeStoreResponses(
            appleIconUrl: 'https://example.test/apple.png',
            googleHtml: '<meta property="og:image" content="https://example.test/google.png">',
        );

        $response = $this->getJson('/api/v1/app-icons?input='.urlencode('https://play.google.com/store/apps/details?id=com.u1.relax.minigame3&hl=uk'));

        $response->assertOk()
            ->assertJsonPath('data.input.type', 'google_play_url')
            ->assertJsonPath('data.input.bundle_id', 'com.u1.relax.minigame3')
            ->assertJsonPath('data.input.apple_app_id', null)
            ->assertJsonPath('data.icons.apple.found', true)
            ->assertJsonPath('data.icons.google.found', true);
    }

    private function fakeStoreResponses(string $appleIconUrl, string $googleHtml): void
    {
        Http::fake([
            'itunes.apple.com/*' => Http::response([
                'resultCount' => 1,
                'results' => [
                    ['artworkUrl512' => $appleIconUrl],
                ],
            ]),
            'play.google.com/*' => Http::response($googleHtml),
        ]);
    }
}
