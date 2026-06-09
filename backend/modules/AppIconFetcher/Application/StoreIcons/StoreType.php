<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Application\StoreIcons;

enum StoreType: string
{
    case Apple = 'apple';
    case Google = 'google';
}
