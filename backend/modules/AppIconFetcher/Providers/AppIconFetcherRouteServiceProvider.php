<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class AppIconFetcherRouteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap module routes.
     */
    public function boot(): void
    {
        Route::middleware('api')
            ->prefix('api/v1')
            ->group($this->modulePath('Presentation/Http/routes/api.php'));

        Route::middleware('web')
            ->group($this->modulePath('Presentation/Http/routes/web.php'));
    }

    private function modulePath(string $path): string
    {
        return dirname(__DIR__).'/'.$path;
    }
}
