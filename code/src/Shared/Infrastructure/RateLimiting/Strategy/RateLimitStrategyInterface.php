<?php

declare(strict_types=1);

namespace Shared\Infrastructure\RateLimiting\Strategy;

/**
 * Request Rate Limiting Strategy Interface.
 */
interface RateLimitStrategyInterface
{
    /**
     * Checks if the request limit has been exceeded.
     *
     * @param string $key               Request ID
     * @param string $resource          Resource ID
     * @param int    $limit             Maximum number of requests
     * @param int    $windowSizeSeconds Time window size in seconds
     *
     * @return bool true if the limit has been exceeded, false if not
     */
    public function isLimitExceeded(string $key, string $resource, int $limit, int $windowSizeSeconds): bool;

    /**
     * Returns information about the current limit state.
     *
     * @param string $key               Request ID
     * @param string $resource          Resource ID
     * @param int    $limit             Maximum number of requests
     * @param int    $windowSizeSeconds Time window size in seconds
     *
     * @return array{
     *     limit: int,
     *     remaining: int,
     *     reset: int,
     *     window_size: int
     * } Limit information
     */
    public function getLimitInfo(string $key, string $resource, int $limit, int $windowSizeSeconds): array;
}
