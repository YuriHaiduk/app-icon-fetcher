<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Application\InputResolvers;

use Modules\AppIconFetcher\Application\Contracts\AppInputTypeResolverInterface;
use Modules\AppIconFetcher\Application\DTO\NormalizedAppInput;
use Modules\AppIconFetcher\Application\Enums\AppInputType;
use Modules\AppIconFetcher\Infrastructure\Exceptions\InvalidAppInputException;

final readonly class AppleAppStoreUrlResolver implements AppInputTypeResolverInterface
{
    public function supports(string $input): bool
    {
        return $this->host($input) === 'apps.apple.com';
    }

    public function resolve(string $input): NormalizedAppInput
    {
        $path = parse_url($input, PHP_URL_PATH);

        if (! is_string($path) || preg_match('/id(\d+)/', $path, $matches) !== 1) {
            throw new InvalidAppInputException('Apple App Store URL must include a valid app ID.');
        }

        return new NormalizedAppInput(
            originalInput: $input,
            type: AppInputType::AppleAppStoreUrl,
            bundleId: null,
            appleAppId: $matches[1],
        );
    }

    private function host(string $input): ?string
    {
        $host = parse_url($input, PHP_URL_HOST);

        return is_string($host) ? strtolower($host) : null;
    }
}
