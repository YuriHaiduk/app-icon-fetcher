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

final class GooglePlayIconClient implements AppIconClientInterface
{
    private const DetailsUrl = 'https://play.google.com/store/apps/details';

    private const UserAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36';

    public function store(): StoreType
    {
        return StoreType::Google;
    }

    public function supports(NormalizedAppInputDto $input): bool
    {
        return $input->bundleId !== null;
    }

    public function fetch(NormalizedAppInputDto $input): StoreIconResultDto
    {
        if ($input->bundleId === null) {
            return StoreIconResultDto::notSupported(StoreType::Google, 'Google Play lookup requires a bundle/package id.');
        }

        try {
            $response = $this->lookup($input->bundleId);

            if ($response->status() === 404) {
                return StoreIconResultDto::notFound(StoreType::Google, 'Icon was not found in Google Play.');
            }

            if ($response->failed()) {
                $this->logFailure($input, 'Google Play request failed with HTTP status '.$response->status().'.');

                return $this->failed();
            }

            return $this->parseResponse($response);
        } catch (Throwable $exception) {
            $this->logFailure($input, $exception->getMessage());

            return $this->failed();
        }
    }

    private function lookup(string $bundleId): Response
    {
        return Http::withUserAgent(self::UserAgent)
            ->timeout(5)
            ->retry(2, 100, throw: false)
            ->get(self::DetailsUrl, [
                'id' => $bundleId,
                'hl' => 'en',
                'gl' => 'US',
            ]);
    }

    private function parseResponse(Response $response): StoreIconResultDto
    {
        $iconUrl = $this->extractIconUrl($response->body());

        if ($iconUrl === null) {
            return StoreIconResultDto::notFound(StoreType::Google, 'Icon URL was not found in Google Play response.');
        }

        return StoreIconResultDto::found(StoreType::Google, $iconUrl);
    }

    private function extractIconUrl(string $html): ?string
    {
        $patterns = [
            '/<meta\b(?=[^>]*\bproperty=["\']og:image["\'])(?=[^>]*\bcontent=["\']([^"\']+)["\'])[^>]*>/i',
            '/<meta\b(?=[^>]*\bname=["\']twitter:image["\'])(?=[^>]*\bcontent=["\']([^"\']+)["\'])[^>]*>/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches) === 1) {
                return $this->normalizeIconUrl($matches[1]);
            }
        }

        return null;
    }

    private function normalizeIconUrl(string $iconUrl): ?string
    {
        $iconUrl = html_entity_decode($iconUrl, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return filter_var($iconUrl, FILTER_VALIDATE_URL) !== false
            && in_array(parse_url($iconUrl, PHP_URL_SCHEME), ['http', 'https'], true)
                ? $iconUrl
                : null;
    }

    private function failed(): StoreIconResultDto
    {
        return StoreIconResultDto::failed(StoreType::Google, 'Google Play is temporarily unavailable.');
    }

    private function logFailure(NormalizedAppInputDto $input, string $message): void
    {
        Log::warning('Google Play icon lookup failed.', [
            'store' => StoreType::Google->value,
            'bundleId' => $input->bundleId,
            'exception' => $message,
        ]);
    }
}
