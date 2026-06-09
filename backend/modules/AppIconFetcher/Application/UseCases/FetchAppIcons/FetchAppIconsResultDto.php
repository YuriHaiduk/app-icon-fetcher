<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Application\UseCases\FetchAppIcons;

use Modules\AppIconFetcher\Application\InputResolving\NormalizedAppInputDto;
use Modules\AppIconFetcher\Application\StoreIcons\StoreIconResultDto;
use Modules\AppIconFetcher\Application\StoreIcons\StoreType;
use RuntimeException;

final readonly class FetchAppIconsResultDto
{
    /**
     * @param  array<string, StoreIconResultDto>  $icons
     */
    public function __construct(
        public NormalizedAppInputDto $input,
        public array $icons,
    ) {}

    public function resultFor(StoreType $store): StoreIconResultDto
    {
        return $this->icons[$store->value]
            ?? throw new RuntimeException(sprintf('Missing icon result for [%s].', $store->value));
    }
}
