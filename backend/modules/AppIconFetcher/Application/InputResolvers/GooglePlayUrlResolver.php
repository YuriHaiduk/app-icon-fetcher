<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Application\InputResolvers;

use Modules\AppIconFetcher\Application\Contracts\AppInputTypeResolverInterface;
use Modules\AppIconFetcher\Application\DTO\NormalizedAppInput;
use Modules\AppIconFetcher\Application\Enums\AppInputType;
use Modules\AppIconFetcher\Infrastructure\Exceptions\InvalidAppInputException;

final readonly class GooglePlayUrlResolver implements AppInputTypeResolverInterface
{
    public function supports(string $input): bool
    {
        return $this->host($input) === 'play.google.com';
    }

    public function resolve(string $input): NormalizedAppInput
    {
        $query = parse_url($input, PHP_URL_QUERY);

        if (! is_string($query)) {
            throw new InvalidAppInputException('Google Play URL must include a valid app ID.');
        }

        parse_str($query, $parameters);

        $bundleId = $parameters['id'] ?? null;

        if (! is_string($bundleId) || ! $this->isValidBundleId($bundleId)) {
            throw new InvalidAppInputException('Google Play URL must include a valid app ID.');
        }

        return new NormalizedAppInput(
            originalInput: $input,
            type: AppInputType::GooglePlayUrl,
            bundleId: $bundleId,
            appleAppId: null,
        );
    }

    private function host(string $input): ?string
    {
        $host = parse_url($input, PHP_URL_HOST);

        return is_string($host) ? strtolower($host) : null;
    }

    private function isValidBundleId(string $bundleId): bool
    {
        return preg_match('/^(?!.*\.\.)(?!\.)(?!.*\.$)[A-Za-z0-9_]+(?:\.[A-Za-z0-9_]+)+$/', $bundleId) === 1;
    }
}
