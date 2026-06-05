<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\AppIconFetcher\Application\DTO\FetchAppIconsResult;
use Modules\AppIconFetcher\Application\DTO\StoreIconResult;

final class FetchAppIconsResource extends JsonResource
{
    /**
     * @return array{
     *     input: array{original: string, type: string, bundle_id: string|null, apple_app_id: string|null},
     *     icons: array{
     *         apple: array{found: bool, icon_url: string|null, message: string|null},
     *         google: array{found: bool, icon_url: string|null, message: string|null}
     *     }
     * }
     */
    public function toArray(Request $request): array
    {
        /** @var FetchAppIconsResult $result */
        $result = $this->resource;

        return [
            'input' => [
                'original' => $result->input->originalInput,
                'type' => $result->input->type->value,
                'bundle_id' => $result->input->bundleId,
                'apple_app_id' => $result->input->appleAppId,
            ],
            'icons' => [
                'apple' => $this->storeResult($result->apple),
                'google' => $this->storeResult($result->google),
            ],
        ];
    }

    /**
     * @return array{found: bool, icon_url: string|null, message: string|null}
     */
    private function storeResult(StoreIconResult $result): array
    {
        return [
            'found' => $result->found,
            'icon_url' => $result->iconUrl,
            'message' => $result->message,
        ];
    }
}
