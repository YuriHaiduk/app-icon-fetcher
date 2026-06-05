<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\AppIconFetcher\Presentation\Http\Controllers\Web\AppIconFetcherPageController;

Route::middleware(['auth', 'verified'])
    ->get('app-icon-fetcher', AppIconFetcherPageController::class)
    ->name('app-icon-fetcher.index');
