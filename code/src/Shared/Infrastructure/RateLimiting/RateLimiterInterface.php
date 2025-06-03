<?php

declare(strict_types=1);

namespace Shared\Infrastructure\RateLimiting;

use Shared\Infrastructure\RateLimiting\Exception\RateLimitExceededException;

/**
 * Interface for checking request rate limits.
 */
interface RateLimiterInterface
{
    /**
     * Checks if a request can be processed given the rate limits.
     *
     * @param string $key      Request identifier (IP, API key, user ID, etc.)
     * @param string $resource Resource identifier (endpoint, controller, etc.)
     *
     * @throws RateLimitExceededException If the request limit is exceeded
     */
    public function check(string $key, string $resource): void;

    /**
     * Returns information about the current limit state.
     *
     * @param string $key      Request identifier
     * @param string $resource Resource identifier
     *
     * @return array<string,mixed> Array with limit information (remaining, reset, limit)
     */
    public function getLimitInfo(string $key, string $resource): array;

    /**
     * Resets the counter for the specified key and resource.
     *
     * @param string $key      Request identifier
     * @param string $resource Resource identifier
     */
    public function reset(string $key, string $resource): void;
}
