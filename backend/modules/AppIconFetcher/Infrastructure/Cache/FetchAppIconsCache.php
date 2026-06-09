<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Infrastructure\Cache;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Modules\AppIconFetcher\Application\UseCases\FetchAppIcons\FetchAppIconsResultDto;
use Modules\AppIconFetcher\Application\InputResolving\NormalizedAppInputDto;
use Modules\AppIconFetcher\Application\StoreIcons\StoreIconResultDto;
use Modules\AppIconFetcher\Application\InputResolving\AppInputType;
use Modules\AppIconFetcher\Application\StoreIcons\StoreType;

final readonly class FetchAppIconsCache
{
    private const CacheTtlSeconds = 86400;

    public function __construct(
        private CacheRepository $cache,
    ) {}

    public function get(NormalizedAppInputDto $input): ?FetchAppIconsResultDto
    {
        $cachedResult = $this->cache->get($this->cacheKey($input));

        if (! is_array($cachedResult)) {
            return null;
        }

        return $this->resultFromCache($cachedResult);
    }

    public function put(NormalizedAppInputDto $input, FetchAppIconsResultDto $result): void
    {
        $this->cache->put($this->cacheKey($input), $this->resultToCache($result), self::CacheTtlSeconds);
    }

    private function cacheKey(NormalizedAppInputDto $input): string
    {
        return sprintf(
            'app_icon_fetcher:%s:%s',
            $input->type->value,
            $input->appleAppId ?? $input->bundleId ?? 'none',
        );
    }

    /**
     * @return array{
     *     input: array{originalInput: string, type: string, bundleId: string|null, appleAppId: string|null},
     *     apple: array{store: string, found: bool, iconUrl: string|null, message: string|null},
     *     google: array{store: string, found: bool, iconUrl: string|null, message: string|null}
     * }
     */
    private function resultToCache(FetchAppIconsResultDto $result): array
    {
        return [
            'input' => [
                'originalInput' => $result->input->originalInput,
                'type' => $result->input->type->value,
                'bundleId' => $result->input->bundleId,
                'appleAppId' => $result->input->appleAppId,
            ],
            'apple' => $this->storeResultToCache($result->apple),
            'google' => $this->storeResultToCache($result->google),
        ];
    }

    /**
     * @param  array{
     *     input: array{originalInput: string, type: string, bundleId: string|null, appleAppId: string|null},
     *     apple: array{store: string, found: bool, iconUrl: string|null, message: string|null},
     *     google: array{store: string, found: bool, iconUrl: string|null, message: string|null}
     * }  $cachedResult
     */
    private function resultFromCache(array $cachedResult): FetchAppIconsResultDto
    {
        return new FetchAppIconsResultDto(
            input: new NormalizedAppInputDto(
                originalInput: $cachedResult['input']['originalInput'],
                type: AppInputType::from($cachedResult['input']['type']),
                bundleId: $cachedResult['input']['bundleId'],
                appleAppId: $cachedResult['input']['appleAppId'],
            ),
            apple: $this->storeResultFromCache($cachedResult['apple']),
            google: $this->storeResultFromCache($cachedResult['google']),
        );
    }

    /**
     * @return array{store: string, found: bool, iconUrl: string|null, message: string|null}
     */
    private function storeResultToCache(StoreIconResultDto $result): array
    {
        return [
            'store' => $result->store->value,
            'found' => $result->found,
            'iconUrl' => $result->iconUrl,
            'message' => $result->message,
        ];
    }

    /**
     * @param  array{store: string, found: bool, iconUrl: string|null, message: string|null}  $cachedResult
     */
    private function storeResultFromCache(array $cachedResult): StoreIconResultDto
    {
        return new StoreIconResultDto(
            store: StoreType::from($cachedResult['store']),
            found: $cachedResult['found'],
            iconUrl: $cachedResult['iconUrl'],
            message: $cachedResult['message'],
        );
    }
}
