<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Application\DTO;

use Modules\AppIconFetcher\Application\Enums\AppInputType;

final readonly class NormalizedAppInput
{
    public function __construct(
        public string $originalInput,
        public AppInputType $type,
        public ?string $bundleId,
        public ?string $appleAppId,
    ) {}
}
