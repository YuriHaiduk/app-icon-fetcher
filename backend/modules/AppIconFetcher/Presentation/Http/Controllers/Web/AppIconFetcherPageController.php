<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Presentation\Http\Controllers\Web;

use Inertia\Response;

final readonly class AppIconFetcherPageController
{
    public function __invoke(): Response
    {
        return inertia('AppIconFetcher/Index');
    }
}
