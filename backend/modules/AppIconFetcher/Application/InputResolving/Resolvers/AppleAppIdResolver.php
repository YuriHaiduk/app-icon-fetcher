<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Application\InputResolving\Resolvers;

use Modules\AppIconFetcher\Application\InputResolving\AppInputTypeResolverInterface;
use Modules\AppIconFetcher\Application\InputResolving\NormalizedAppInputDto;
use Modules\AppIconFetcher\Application\InputResolving\AppInputType;
use Modules\AppIconFetcher\Infrastructure\Exceptions\InvalidAppInputException;

final readonly class AppleAppIdResolver implements AppInputTypeResolverInterface
{
    public function supports(string $input): bool
    {
        return preg_match('/^(?:id)?\d{5,20}$/i', $input) === 1;
    }

    public function resolve(string $input): NormalizedAppInputDto
    {
        if (preg_match('/^(?:id)?(\d{5,20})$/i', $input, $matches) !== 1) {
            throw new InvalidAppInputException('Please provide a valid bundle ID or supported app store URL.');
        }

        return new NormalizedAppInputDto(
            originalInput: $input,
            type: AppInputType::AppleAppId,
            bundleId: null,
            appleAppId: $matches[1],
        );
    }
}
