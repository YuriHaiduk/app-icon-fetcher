<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Application\InputResolving;

use Modules\AppIconFetcher\Application\InputResolving\NormalizedAppInputDto;

interface AppInputTypeResolverInterface
{
    public function supports(string $input): bool;

    public function resolve(string $input): NormalizedAppInputDto;
}
