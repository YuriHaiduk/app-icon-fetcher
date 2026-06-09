<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Application\UseCases\FetchAppIcons;

use Modules\AppIconFetcher\Application\InputResolving\AppInputResolver;
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
            apple: $this->appleClient->fetch($normalizedInput),
            google: $this->googleClient->fetch($normalizedInput),
        );

        $this->cache->put($normalizedInput, $result);

        return $result;
    }
}
