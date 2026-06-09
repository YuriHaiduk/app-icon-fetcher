<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Application\InputResolving;

enum AppInputType: string
{
    case BundleId = 'bundle_id';
    case AppleAppId = 'apple_app_id';
    case GooglePlayUrl = 'google_play_url';
    case AppleAppStoreUrl = 'apple_app_store_url';
}
