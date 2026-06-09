<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Application\InputResolvers;

use Modules\AppIconFetcher\Application\Contracts\AppInputTypeResolverInterface;
use Modules\AppIconFetcher\Application\DTO\NormalizedAppInput;
use Modules\AppIconFetcher\Application\Enums\AppInputType;
use Modules\AppIconFetcher\Infrastructure\Exceptions\InvalidAppInputException;

final readonly class AppleAppIdResolver implements AppInputTypeResolverInterface
{
    public function supports(string $input): bool
    {
        return preg_match('/^(?:id)?\d{5,20}$/i', $input) === 1;
    }

    public function resolve(string $input): NormalizedAppInput
    {
        if (preg_match('/^(?:id)?(\d{5,20})$/i', $input, $matches) !== 1) {
            throw new InvalidAppInputException('Please provide a valid bundle ID or supported app store URL.');
        }

        return new NormalizedAppInput(
            originalInput: $input,
            type: AppInputType::AppleAppId,
            bundleId: null,
            appleAppId: $matches[1],
        );
    }
}
