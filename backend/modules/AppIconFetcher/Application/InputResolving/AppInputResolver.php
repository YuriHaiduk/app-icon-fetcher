<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Application\InputResolving;

use Modules\AppIconFetcher\Application\InputResolving\AppInputTypeResolverInterface;
use Modules\AppIconFetcher\Application\InputResolving\NormalizedAppInputDto;
use Modules\AppIconFetcher\Infrastructure\Exceptions\InvalidAppInputException;

final readonly class AppInputResolver
{
    /**
     * @param  iterable<AppInputTypeResolverInterface>  $resolvers
     */
    public function __construct(
        private iterable $resolvers,
    ) {}

    public function resolve(string $input): NormalizedAppInputDto
    {
        $originalInput = trim($input);

        if ($originalInput === '') {
            throw new InvalidAppInputException('Please provide an app store URL or bundle ID.');
        }

        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($originalInput)) {
                return $resolver->resolve($originalInput);
            }
        }

        throw $this->invalidInputException($originalInput);
    }

    private function invalidInputException(string $input): InvalidAppInputException
    {
        if (! $this->looksLikeUrl($input)) {
            return new InvalidAppInputException('Please provide a valid bundle ID or supported app store URL.');
        }

        if (parse_url($input, PHP_URL_HOST) === null) {
            return new InvalidAppInputException('Please provide a valid app store URL.');
        }

        return new InvalidAppInputException('This app store URL is not supported.');
    }

    private function looksLikeUrl(string $input): bool
    {
        return parse_url($input, PHP_URL_SCHEME) !== null || parse_url($input, PHP_URL_HOST) !== null;
    }
}
