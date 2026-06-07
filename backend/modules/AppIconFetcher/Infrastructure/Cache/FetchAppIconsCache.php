<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Infrastructure\Cache;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Modules\AppIconFetcher\Application\DTO\FetchAppIconsResult;
use Modules\AppIconFetcher\Application\DTO\NormalizedAppInput;
use Modules\AppIconFetcher\Application\DTO\StoreIconResult;
use Modules\AppIconFetcher\Application\Enums\AppInputType;
use Modules\AppIconFetcher\Application\Enums\StoreType;

final readonly class FetchAppIconsCache
{
    private const CacheTtlSeconds = 86400;

    public function __construct(
        private CacheRepository $cache,
    ) {}

    public function get(NormalizedAppInput $input): ?FetchAppIconsResult
    {
        $cachedResult = $this->cache->get($this->cacheKey($input));

        if (! is_array($cachedResult)) {
            return null;
        }

        return $this->resultFromCache($cachedResult);
    }

    public function put(NormalizedAppInput $input, FetchAppIconsResult $result): void
    {
        $this->cache->put($this->cacheKey($input), $this->resultToCache($result), self::CacheTtlSeconds);
    }

    private function cacheKey(NormalizedAppInput $input): string
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
    private function resultToCache(FetchAppIconsResult $result): array
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
    private function resultFromCache(array $cachedResult): FetchAppIconsResult
    {
        return new FetchAppIconsResult(
            input: new NormalizedAppInput(
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
    private function storeResultToCache(StoreIconResult $result): array
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
    private function storeResultFromCache(array $cachedResult): StoreIconResult
    {
        return new StoreIconResult(
            store: StoreType::from($cachedResult['store']),
            found: $cachedResult['found'],
            iconUrl: $cachedResult['iconUrl'],
            message: $cachedResult['message'],
        );
    }
}
