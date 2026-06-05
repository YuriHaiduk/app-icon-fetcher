<?php

declare(strict_types=1);

namespace Modules\AppIconFetcher\Application\DTO;

use Modules\AppIconFetcher\Application\Enums\StoreType;

final readonly class StoreIconResult
{
    public function __construct(
        public StoreType $store,
        public bool $found,
        public ?string $iconUrl,
        public ?string $message,
    ) {}

    public static function found(StoreType $store, string $iconUrl): self
    {
        return new self(
            store: $store,
            found: true,
            iconUrl: $iconUrl,
            message: null,
        );
    }

    public static function notFound(StoreType $store, string $message): self
    {
        return new self(
            store: $store,
            found: false,
            iconUrl: null,
            message: $message,
        );
    }

    public static function notSupported(StoreType $store, string $message): self
    {
        return new self(
            store: $store,
            found: false,
            iconUrl: null,
            message: $message,
        );
    }

    public static function failed(StoreType $store, string $message): self
    {
        return new self(
            store: $store,
            found: false,
            iconUrl: null,
            message: $message,
        );
    }
}
