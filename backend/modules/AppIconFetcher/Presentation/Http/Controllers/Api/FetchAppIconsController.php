<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Presentation\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\AppIconFetcher\Application\Services\FetchAppIconsService;
use Modules\AppIconFetcher\Infrastructure\Exceptions\InvalidAppInputException;
use Modules\AppIconFetcher\Presentation\Http\Requests\FetchAppIconsRequest;
use Modules\AppIconFetcher\Presentation\Http\Resources\FetchAppIconsResource;

final readonly class FetchAppIconsController
{
    public function __construct(
        private FetchAppIconsService $fetchAppIcons,
    ) {}

    public function __invoke(FetchAppIconsRequest $request): FetchAppIconsResource|JsonResponse
    {
        try {
            return new FetchAppIconsResource(
                $this->fetchAppIcons->fetch($request->appInput()),
            );
        } catch (InvalidAppInputException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'errors' => [
                    'input' => [$exception->getMessage()],
                ],
            ], 422);
        }
    }
}
