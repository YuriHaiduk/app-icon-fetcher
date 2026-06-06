<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Infrastructure\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

final class InvalidAppInputException extends RuntimeException
{
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => [
                'input' => [$this->getMessage()],
            ],
        ], 422);
    }
}
