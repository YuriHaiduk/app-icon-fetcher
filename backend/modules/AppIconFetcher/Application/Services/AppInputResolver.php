<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Application\Services;

use Modules\AppIconFetcher\Application\DTO\NormalizedAppInput;
use Modules\AppIconFetcher\Application\Enums\AppInputType;
use Modules\AppIconFetcher\Infrastructure\Exceptions\InvalidAppInputException;

final readonly class AppInputResolver
{
    public function resolve(string $input): NormalizedAppInput
    {
        $originalInput = trim($input);

        if ($originalInput === '') {
            throw new InvalidAppInputException('Please provide an app store URL or bundle ID.');
        }

        if ($this->looksLikeUrl($originalInput)) {
            return $this->resolveUrl($originalInput);
        }

        if ($this->isValidAppleAppId($originalInput)) {
            return $this->resolveAppleAppId($originalInput);
        }

        if (! $this->isValidBundleId($originalInput)) {
            throw new InvalidAppInputException('Please provide a valid bundle ID or supported app store URL.');
        }

        return new NormalizedAppInput(
            originalInput: $originalInput,
            type: AppInputType::BundleId,
            bundleId: $originalInput,
            appleAppId: null,
        );
    }

    private function resolveAppleAppId(string $input): NormalizedAppInput
    {
        if (preg_match('/^(?:id)?(\d{5,20})$/i', $input, $matches) !== 1) {
            throw new InvalidAppInputException('Please provide a valid bundle ID or supported app store URL.');
        }

        return new NormalizedAppInput(
            originalInput: $input,
            type: AppInputType::AppleAppId,
            bundleId: null,
            appleAppId: $matches[1],
        );
    }

    private function resolveUrl(string $input): NormalizedAppInput
    {
        $host = $this->parseHost($input);

        return match ($host) {
            'play.google.com' => $this->resolveGooglePlayUrl($input),
            'apps.apple.com' => $this->resolveAppleAppStoreUrl($input),
            default => throw new InvalidAppInputException('This app store URL is not supported.'),
        };
    }

    private function resolveGooglePlayUrl(string $input): NormalizedAppInput
    {
        $query = parse_url($input, PHP_URL_QUERY);

        if (! is_string($query)) {
            throw new InvalidAppInputException('Google Play URL must include a valid app ID.');
        }

        parse_str($query, $parameters);

        $bundleId = $parameters['id'] ?? null;

        if (! is_string($bundleId) || ! $this->isValidBundleId($bundleId)) {
            throw new InvalidAppInputException('Google Play URL must include a valid app ID.');
        }

        return new NormalizedAppInput(
            originalInput: $input,
            type: AppInputType::GooglePlayUrl,
            bundleId: $bundleId,
            appleAppId: null,
        );
    }

    private function resolveAppleAppStoreUrl(string $input): NormalizedAppInput
    {
        $path = parse_url($input, PHP_URL_PATH);

        if (! is_string($path) || preg_match('/id(\d+)/', $path, $matches) !== 1) {
            throw new InvalidAppInputException('Apple App Store URL must include a valid app ID.');
        }

        return new NormalizedAppInput(
            originalInput: $input,
            type: AppInputType::AppleAppStoreUrl,
            bundleId: null,
            appleAppId: $matches[1],
        );
    }

    private function looksLikeUrl(string $input): bool
    {
        return parse_url($input, PHP_URL_SCHEME) !== null || parse_url($input, PHP_URL_HOST) !== null;
    }

    private function parseHost(string $input): string
    {
        $host = parse_url($input, PHP_URL_HOST);

        if (! is_string($host)) {
            throw new InvalidAppInputException('Please provide a valid app store URL.');
        }

        return strtolower($host);
    }

    private function isValidBundleId(string $bundleId): bool
    {
        return preg_match('/^(?!.*\.\.)(?!\.)(?!.*\.$)[A-Za-z0-9_]+(?:\.[A-Za-z0-9_]+)+$/', $bundleId) === 1;
    }

    private function isValidAppleAppId(string $input): bool
    {
        return preg_match('/^(?:id)?\d{5,20}$/i', $input) === 1;
    }
}
