<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Application\StoreIcons;

use Modules\AppIconFetcher\Application\InputResolving\NormalizedAppInputDto;

interface AppIconProviderInterface
{
    public function store(): StoreType;

    public function fetch(NormalizedAppInputDto $input): StoreIconResultDto;
}
