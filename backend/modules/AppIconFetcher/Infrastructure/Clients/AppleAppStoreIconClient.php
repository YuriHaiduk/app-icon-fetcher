<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Infrastructure\Clients;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\AppIconFetcher\Application\InputResolving\NormalizedAppInputDto;
use Modules\AppIconFetcher\Application\StoreIcons\StoreIconResultDto;
use Modules\AppIconFetcher\Application\StoreIcons\StoreType;
use Modules\AppIconFetcher\Infrastructure\Contracts\AppIconClientInterface;
use Throwable;

final class AppleAppStoreIconClient implements AppIconClientInterface
{
    private const LookupUrl = 'https://itunes.apple.com/lookup';

    public function store(): StoreType
    {
        return StoreType::Apple;
    }

    public function supports(NormalizedAppInputDto $input): bool
    {
        return $input->appleAppId !== null || $input->bundleId !== null;
    }

    public function fetch(NormalizedAppInputDto $input): StoreIconResultDto
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

    private function lookup(NormalizedAppInputDto $input): Response
    {
        return Http::acceptJson()
            ->timeout(5)
            ->retry(2, 100, throw: false)
            ->get(self::LookupUrl, $this->lookupQuery($input));
    }

    /**
     * @return array{id?: string, bundleId?: string}
     */
    private function lookupQuery(NormalizedAppInputDto $input): array
    {
        if ($input->appleAppId !== null) {
            return ['id' => $input->appleAppId];
        }

        if ($input->bundleId !== null) {
            return ['bundleId' => $input->bundleId];
        }

        return [];
    }

    private function parseResponse(Response $response): StoreIconResultDto
    {
        $payload = $response->json();

        if (! is_array($payload)) {
            return $this->failed();
        }

        $results = $payload['results'] ?? [];

        if (($payload['resultCount'] ?? 0) === 0 || ! is_array($results) || $results === []) {
            return StoreIconResultDto::notFound(StoreType::Apple, 'Icon was not found in Apple App Store.');
        }

        $iconUrl = $this->extractIconUrl($results[0]);

        if ($iconUrl === null) {
            return StoreIconResultDto::notFound(StoreType::Apple, 'Icon URL was not found in Apple App Store response.');
        }

        return StoreIconResultDto::found(StoreType::Apple, $iconUrl);
    }

    private function extractIconUrl(mixed $result): ?string
    {
        if (! is_array($result)) {
            return null;
        }

        $iconUrl = $result['artworkUrl512'] ?? $result['artworkUrl100'] ?? null;

        return is_string($iconUrl) && $iconUrl !== '' ? $iconUrl : null;
    }

    private function failed(): StoreIconResultDto
    {
        return StoreIconResultDto::failed(StoreType::Apple, 'Apple App Store is temporarily unavailable.');
    }

    private function logFailure(NormalizedAppInputDto $input, string $message): void
    {
        Log::warning('Apple App Store icon lookup failed.', [
            'store' => StoreType::Apple->value,
            'bundleId' => $input->bundleId,
            'appleAppId' => $input->appleAppId,
            'exception' => $message,
        ]);
    }
}
