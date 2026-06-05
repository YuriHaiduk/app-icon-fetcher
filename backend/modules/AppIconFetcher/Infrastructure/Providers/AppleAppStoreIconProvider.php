<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Infrastructure\Providers;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\AppIconFetcher\Application\DTO\NormalizedAppInput;
use Modules\AppIconFetcher\Application\DTO\StoreIconResult;
use Modules\AppIconFetcher\Application\Enums\StoreType;
use Modules\AppIconFetcher\Infrastructure\Contracts\AppIconProviderInterface;
use Throwable;

final class AppleAppStoreIconProvider implements AppIconProviderInterface
{
    private const LookupUrl = 'https://itunes.apple.com/lookup';

    public function store(): StoreType
    {
        return StoreType::Apple;
    }

    public function supports(NormalizedAppInput $input): bool
    {
        return $input->appleAppId !== null || $input->bundleId !== null;
    }

    public function fetch(NormalizedAppInput $input): StoreIconResult
    {
        try {
            $response = $this->lookup($input);

            if ($response->failed()) {
                $this->logFailure($input, 'Apple lookup request failed with HTTP status '.$response->status().'.');

                return $this->failed();
            }

            return $this->parseResponse($response);
        } catch (Throwable $exception) {
            $this->logFailure($input, $exception->getMessage());

            return $this->failed();
        }
    }

    private function lookup(NormalizedAppInput $input): Response
    {
        return Http::acceptJson()
            ->timeout(5)
            ->retry(2, 100, throw: false)
            ->get(self::LookupUrl, $this->lookupQuery($input));
    }

    /**
     * @return array{id?: string, bundleId?: string}
     */
    private function lookupQuery(NormalizedAppInput $input): array
    {
        if ($input->appleAppId !== null) {
            return ['id' => $input->appleAppId];
        }

        if ($input->bundleId !== null) {
            return ['bundleId' => $input->bundleId];
        }

        return [];
    }

    private function parseResponse(Response $response): StoreIconResult
    {
        $payload = $response->json();

        if (! is_array($payload)) {
            return $this->failed();
        }

        $results = $payload['results'] ?? [];

        if (($payload['resultCount'] ?? 0) === 0 || ! is_array($results) || $results === []) {
            return StoreIconResult::notFound(StoreType::Apple, 'Icon was not found in Apple App Store.');
        }

        $iconUrl = $this->extractIconUrl($results[0]);

        if ($iconUrl === null) {
            return StoreIconResult::notFound(StoreType::Apple, 'Icon URL was not found in Apple App Store response.');
        }

        return StoreIconResult::found(StoreType::Apple, $iconUrl);
    }

    private function extractIconUrl(mixed $result): ?string
    {
        if (! is_array($result)) {
            return null;
        }

        $iconUrl = $result['artworkUrl512'] ?? $result['artworkUrl100'] ?? null;

        return is_string($iconUrl) && $iconUrl !== '' ? $iconUrl : null;
    }

    private function failed(): StoreIconResult
    {
        return StoreIconResult::failed(StoreType::Apple, 'Apple App Store is temporarily unavailable.');
    }

    private function logFailure(NormalizedAppInput $input, string $message): void
    {
        Log::warning('Apple App Store icon lookup failed.', [
            'store' => StoreType::Apple->value,
            'bundleId' => $input->bundleId,
            'appleAppId' => $input->appleAppId,
            'exception' => $message,
        ]);
    }
}
