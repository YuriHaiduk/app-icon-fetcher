<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Infrastructure\Contracts;

use Modules\AppIconFetcher\Application\DTO\NormalizedAppInput;
use Modules\AppIconFetcher\Application\DTO\StoreIconResult;
use Modules\AppIconFetcher\Application\Enums\StoreType;

interface AppIconClientInterface
{
    public function store(): StoreType;

    public function supports(NormalizedAppInput $input): bool;

    public function fetch(NormalizedAppInput $input): StoreIconResult;
}
