<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use Modules\AppIconFetcher\AppIconFetcherServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    AppIconFetcherServiceProvider::class,
];
