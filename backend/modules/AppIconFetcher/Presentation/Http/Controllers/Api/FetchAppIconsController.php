<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Presentation\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\AppIconFetcher\Application\Services\FetchAppIconsService;
use Modules\AppIconFetcher\Presentation\Http\Requests\FetchAppIconsRequest;
use Modules\AppIconFetcher\Presentation\Http\Resources\FetchAppIconsResource;

final readonly class FetchAppIconsController
{
    public function __construct(
        private FetchAppIconsService $fetchAppIcons,
    ) {}

    public function __invoke(FetchAppIconsRequest $request): JsonResponse
    {
        return new FetchAppIconsResource(
            $this->fetchAppIcons->fetch($request->appInput()),
        )
            ->response()
            ->setStatusCode(200);
    }
}
