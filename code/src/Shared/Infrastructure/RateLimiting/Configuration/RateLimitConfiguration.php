<?php

declare(strict_types=1);

namespace Shared\Infrastructure\RateLimiting\Configuration;

final readonly class RateLimitConfiguration
{
    public function __construct(
        private int $limit,
        private int $windowSizeSeconds,
        private string $resource,
    ) {
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getWindowSizeSeconds(): int
    {
        return $this->windowSizeSeconds;
    }

    public function getResource(): string
    {
        return $this->resource;
    }
}
