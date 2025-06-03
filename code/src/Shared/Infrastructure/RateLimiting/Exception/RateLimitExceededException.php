<?php

declare(strict_types=1);

namespace Shared\Infrastructure\RateLimiting\Exception;

/**
 * Exception thrown when rate limit is exceeded.
 */
final class RateLimitExceededException extends \RuntimeException
{
    /**
     * @param string               $resource        The resource that was rate limited
     * @param int                  $limitValue      The rate limit value that was exceeded
     * @param int                  $waitTimeSeconds The time in seconds until the rate limit resets
     * @param array<string, mixed> $context         Additional context information
     */
    public function __construct(
        private readonly string $resource,
        private readonly int $limitValue,
        private readonly int $waitTimeSeconds,
        private readonly array $context = [],
    ) {
        parent::__construct(
            sprintf(
                'Rate limit exceeded for resource "%s". Limit: %d. Wait %d seconds before retrying.',
                $resource,
                $limitValue,
                $waitTimeSeconds
            ),
            429 // HTTP 429 Too Many Requests
        );
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function getLimitValue(): int
    {
        return $this->limitValue;
    }

    public function getWaitTimeSeconds(): int
    {
        return $this->waitTimeSeconds;
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
