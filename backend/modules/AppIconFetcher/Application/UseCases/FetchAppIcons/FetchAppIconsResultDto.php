<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Application\UseCases\FetchAppIcons;

use Modules\AppIconFetcher\Application\InputResolving\NormalizedAppInputDto;
use Modules\AppIconFetcher\Application\StoreIcons\StoreIconResultDto;

final readonly class FetchAppIconsResultDto
{
    public function __construct(
        public NormalizedAppInputDto $input,
        public StoreIconResultDto $apple,
        public StoreIconResultDto $google,
    ) {}
}
