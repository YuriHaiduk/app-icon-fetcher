<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Application\Contracts;

use Modules\AppIconFetcher\Application\DTO\NormalizedAppInput;

interface AppInputTypeResolverInterface
{
    public function supports(string $input): bool;

    public function resolve(string $input): NormalizedAppInput;
}
