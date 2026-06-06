<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Application\Services;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Modules\AppIconFetcher\Application\DTO\FetchAppIconsResult;
use Modules\AppIconFetcher\Application\DTO\NormalizedAppInput;
use Modules\AppIconFetcher\Application\DTO\StoreIconResult;
use Modules\AppIconFetcher\Application\Enums\AppInputType;
use Modules\AppIconFetcher\Application\Enums\StoreType;
use Modules\AppIconFetcher\Infrastructure\Contracts\AppIconClientInterface;

final readonly class FetchAppIconsService
{
    private const CacheTtlSeconds = 86400;

    public function __construct(
        private AppInputResolver $inputResolver,
        private AppIconClientInterface $appleClient,
        private AppIconClientInterface $googleClient,
        private CacheRepository $cache,
    ) {}

    public function fetch(string $input): FetchAppIconsResult
    {
        $normalizedInput = $this->inputResolver->resolve($input);
        $cacheKey = $this->cacheKey($normalizedInput);
        $cachedResult = $this->cache->get($cacheKey);

        if (is_array($cachedResult)) {
            return $this->resultFromCache($cachedResult);
        }

        $result = new FetchAppIconsResult(
            input: $normalizedInput,
            apple: $this->fetchClient($normalizedInput, $this->appleClient),
            google: $this->fetchClient($normalizedInput, $this->googleClient),
        );

        $this->cache->put($cacheKey, $this->resultToCache($result), self::CacheTtlSeconds);

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

    private function cacheKey(NormalizedAppInput $input): string
    {
        return sprintf(
            'app_icon_fetcher:%s:%s',
            $input->type->value,
            $input->appleAppId ?? $input->bundleId ?? 'none',
        );
    }

    private function notSupportedMessage(StoreType $store): string
    {
        return match ($store) {
            StoreType::Apple => 'Apple App Store lookup requires an Apple app id or bundle/package id.',
            StoreType::Google => 'Google Play lookup requires a bundle/package id.',
        };
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
