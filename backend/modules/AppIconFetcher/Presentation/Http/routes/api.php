<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\AppIconFetcher\Presentation\Http\Controllers\Api\FetchAppIconsController;

Route::get('app-icons', FetchAppIconsController::class)
    ->name('app-icon-fetcher.api.fetch');
