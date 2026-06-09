<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Infrastructure\Contracts;

use Modules\AppIconFetcher\Application\InputResolving\NormalizedAppInputDto;
use Modules\AppIconFetcher\Application\StoreIcons\StoreIconResultDto;
use Modules\AppIconFetcher\Application\StoreIcons\StoreType;

interface AppIconClientInterface
{
    public function store(): StoreType;

    public function supports(NormalizedAppInputDto $input): bool;

    public function fetch(NormalizedAppInputDto $input): StoreIconResultDto;
}
