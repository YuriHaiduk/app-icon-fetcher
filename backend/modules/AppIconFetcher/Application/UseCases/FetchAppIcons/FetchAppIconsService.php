<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Application\UseCases\FetchAppIcons;

use Modules\AppIconFetcher\Application\InputResolving\AppInputResolver;
use Modules\AppIconFetcher\Application\InputResolving\NormalizedAppInputDto;
use Modules\AppIconFetcher\Application\StoreIcons\StoreIconResultDto;
use Modules\AppIconFetcher\Application\StoreIcons\StoreType;
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

    public function fetch(string $input): FetchAppIconsResultDto
    {
        $normalizedInput = $this->inputResolver->resolve($input);
        $cachedResult = $this->cache->get($normalizedInput);

        if ($cachedResult !== null) {
            return $cachedResult;
        }

        $result = new FetchAppIconsResultDto(
            input: $normalizedInput,
            apple: $this->fetchClient($normalizedInput, $this->appleClient),
            google: $this->fetchClient($normalizedInput, $this->googleClient),
        );

        $this->cache->put($normalizedInput, $result);

        return $result;
    }

    private function fetchClient(NormalizedAppInputDto $input, AppIconClientInterface $client): StoreIconResultDto
    {
        $store = $client->store();

        if (! $client->supports($input)) {
            return StoreIconResultDto::notSupported($store, $this->notSupportedMessage($store));
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
