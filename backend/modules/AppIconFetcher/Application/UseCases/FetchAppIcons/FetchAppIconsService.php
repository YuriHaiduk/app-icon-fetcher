<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Application\UseCases\FetchAppIcons;

use Modules\AppIconFetcher\Application\InputResolving\AppInputResolver;
use Modules\AppIconFetcher\Application\InputResolving\NormalizedAppInputDto;
use Modules\AppIconFetcher\Application\StoreIcons\AppIconProviderInterface;
use Modules\AppIconFetcher\Application\StoreIcons\StoreIconResultDto;
use Modules\AppIconFetcher\Infrastructure\Cache\FetchAppIconsCache;

final readonly class FetchAppIconsService
{
    /**
     * @param  iterable<AppIconProviderInterface>  $providers
     */
    public function __construct(
        private AppInputResolver $inputResolver,
        private iterable $providers,
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
            icons: $this->fetchIcons($normalizedInput),
        );

        $this->cache->put($normalizedInput, $result);

        return $result;
    }

    /**
     * @return array<string, StoreIconResultDto>
     */
    private function fetchIcons(NormalizedAppInputDto $normalizedInput): array
    {
        $icons = [];

        foreach ($this->providers as $provider) {
            $icons[$provider->store()->value] = $provider->fetch($normalizedInput);
        }

        return $icons;
    }
}
