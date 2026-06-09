<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Application\InputResolving\Resolvers;

use Modules\AppIconFetcher\Application\InputResolving\AppInputTypeResolverInterface;
use Modules\AppIconFetcher\Application\InputResolving\NormalizedAppInputDto;
use Modules\AppIconFetcher\Application\InputResolving\AppInputType;
use Modules\AppIconFetcher\Infrastructure\Exceptions\InvalidAppInputException;

final readonly class BundleIdResolver implements AppInputTypeResolverInterface
{
    public function supports(string $input): bool
    {
        return $this->isValidBundleId($input);
    }

    public function resolve(string $input): NormalizedAppInputDto
    {
        if (! $this->isValidBundleId($input)) {
            throw new InvalidAppInputException('Please provide a valid bundle ID or supported app store URL.');
        }

        return new NormalizedAppInputDto(
            originalInput: $input,
            type: AppInputType::BundleId,
            bundleId: $input,
            appleAppId: null,
        );
    }

    private function isValidBundleId(string $bundleId): bool
    {
        return preg_match('/^(?!.*\.\.)(?!\.)(?!.*\.$)[A-Za-z0-9_]+(?:\.[A-Za-z0-9_]+)+$/', $bundleId) === 1;
    }
}
