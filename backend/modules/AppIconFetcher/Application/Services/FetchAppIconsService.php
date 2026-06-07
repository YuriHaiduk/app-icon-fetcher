<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Application\Services;

use Modules\AppIconFetcher\Application\DTO\FetchAppIconsResult;
use Modules\AppIconFetcher\Application\DTO\NormalizedAppInput;
use Modules\AppIconFetcher\Application\DTO\StoreIconResult;
use Modules\AppIconFetcher\Application\Enums\StoreType;
use Modules\AppIconFetcher\Infrastructure\Cache\FetchAppIconsCache;
use Modules\AppIconFetcher\Infrastructure\Contracts\AppIconClientInterface;

final readonly class FetchAppIconsService
{
    public function __construct(
        private AppInputResolver $inputResolver,
        private AppIconClientInterface $appleClient,
        private AppIconClientInterface $googleClient,
        private FetchAppIconsCache $cache,
    ) {}

    public function fetch(string $input): FetchAppIconsResult
    {
        $normalizedInput = $this->inputResolver->resolve($input);
        $cachedResult = $this->cache->get($normalizedInput);

        if ($cachedResult !== null) {
            return $cachedResult;
        }

        $result = new FetchAppIconsResult(
            input: $normalizedInput,
            apple: $this->fetchClient($normalizedInput, $this->appleClient),
            google: $this->fetchClient($normalizedInput, $this->googleClient),
        );

        $this->cache->put($normalizedInput, $result);

        return $result;
    }

    private function fetchClient(NormalizedAppInput $input, AppIconClientInterface $client): StoreIconResult
    {
        $store = $client->store();

        if (! $client->supports($input)) {
            return StoreIconResult::notSupported($store, $this->notSupportedMessage($store));
        }

        return $client->fetch($input);
    }

    private function notSupportedMessage(StoreType $store): string
    {
        return match ($store) {
            StoreType::Apple => 'Apple App Store lookup requires an Apple app id or bundle/package id.',
            StoreType::Google => 'Google Play lookup requires a bundle/package id.',
        };
    }
}
