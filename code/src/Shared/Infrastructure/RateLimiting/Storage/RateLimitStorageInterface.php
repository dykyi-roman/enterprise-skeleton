<?php

declare(strict_types=1);

namespace Shared\Infrastructure\RateLimiting\Storage;

/**
 * Interface for rate limiting storage implementations.
 */
interface RateLimitStorageInterface
{
    /**
     * Increments the request counter for the specified key and resource.
     *
     * @param string $key               Request identifier (IP, API key, etc.)
     * @param string $resource          Resource identifier (endpoint, etc.)
     * @param int    $windowSizeSeconds Time window size in seconds
     *
     * @return int Current number of requests
     */
    public function increment(string $key, string $resource, int $windowSizeSeconds): int;

    /**
     * Gets the current number of requests for the specified key and resource.
     *
     * @param string $key      Request ID
     * @param string $resource Resource ID
     *
     * @return int Current number of requests
     */
    public function get(string $key, string $resource): int;

    /**
     * Returns the time to live in seconds for the rate limit counter.
     *
     * @param string $key      Request ID
     * @param string $resource Resource ID
     *
     * @return int Seconds until expiration (0 if expired or not found)
     */
    public function getTimeToLive(string $key, string $resource): int;

    /**
     * Resets the counter for the specified key and resource.
     *
     * @param string $key      Request ID
     * @param string $resource Resource ID
     *
     * @return bool True if reset was successful, false otherwise
     */
    public function reset(string $key, string $resource): bool;
}
