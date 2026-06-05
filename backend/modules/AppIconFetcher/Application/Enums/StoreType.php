<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Application\Enums;

enum StoreType: string
{
    case Apple = 'apple';
    case Google = 'google';
}
