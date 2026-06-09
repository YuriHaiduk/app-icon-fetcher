<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Application\InputResolving;

use Modules\AppIconFetcher\Application\InputResolving\AppInputType;

final readonly class NormalizedAppInputDto
{
    public function __construct(
        public string $originalInput,
        public AppInputType $type,
        public ?string $bundleId,
        public ?string $appleAppId,
    ) {}
}
