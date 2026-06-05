<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Application\DTO;

final readonly class FetchAppIconsResult
{
    public function __construct(
        public NormalizedAppInput $input,
        public StoreIconResult $apple,
        public StoreIconResult $google,
    ) {}
}
