<?php

declare(strict_types=1);

namespace Shared\Infrastructure\RateLimiting;

use Shared\Infrastructure\RateLimiting\Configuration\RateLimitConfiguration;
use Shared\Infrastructure\RateLimiting\Storage\RateLimitStorageInterface;
use Shared\Infrastructure\RateLimiting\Strategy\FixedWindowStrategy;
use Shared\Infrastructure\RateLimiting\Strategy\RateLimitStrategyInterface;

final readonly class RateLimiterFactory
{
    public function __construct(
        private RateLimitStorageInterface $storage,
    ) {
    }

    /**
     * @param string                          $resource          Resource identifier
     * @param int                             $limit             Maximum number of requests
     * @param int                             $windowSizeSeconds Time window size in seconds
     * @param RateLimitStrategyInterface|null $strategy          Rate limiting strategy (optional)
     */
    public function create(
        string $resource,
        int $limit,
        int $windowSizeSeconds,
        ?RateLimitStrategyInterface $strategy = null,
    ): RateLimiter {
        $configuration = new RateLimitConfiguration(
            $limit,
            $windowSizeSeconds,
            $resource
        );

        $strategy = $strategy ?? new FixedWindowStrategy($this->storage);

        return new RateLimiter($strategy, $configuration);
    }

    /**
     * Creates a RateLimiter for an API route.
     *
     * @param string $routeName         API route name
     * @param int    $limit             Maximum number of requests
     * @param int    $windowSizeSeconds Time window size in seconds
     */
    public function createForApiRoute(string $routeName, int $limit, int $windowSizeSeconds): RateLimiter
    {
        $resource = 'api_route:'.$routeName;

        return $this->create($resource, $limit, $windowSizeSeconds);
    }
}
