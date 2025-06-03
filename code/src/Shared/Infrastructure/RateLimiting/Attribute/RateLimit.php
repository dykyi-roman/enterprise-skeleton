<?php

declare(strict_types=1);

namespace Shared\Infrastructure\RateLimiting\Attribute;

use Attribute;

/**
 * Attribute for applying rate limiting to API controllers and methods.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
final class RateLimit
{
    /**
     * @param int         $limit             Maximum number of requests allowed
     * @param int         $windowSizeSeconds Time window size in seconds
     * @param string|null $key               Custom key for rate limiting (optional, defaults to IP)
     */
    public function __construct(
        private readonly int $limit,
        private readonly int $windowSizeSeconds = 60,
        private readonly ?string $key = null,
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

    public function getKey(): ?string
    {
        return $this->key;
    }
}
