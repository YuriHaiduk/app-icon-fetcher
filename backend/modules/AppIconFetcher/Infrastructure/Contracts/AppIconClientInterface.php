<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Infrastructure\Contracts;

use Modules\AppIconFetcher\Application\InputResolving\NormalizedAppInputDto;
use Modules\AppIconFetcher\Application\StoreIcons\StoreIconResultDto;

interface AppIconClientInterface
{
    public function fetch(NormalizedAppInputDto $input): StoreIconResultDto;
}
