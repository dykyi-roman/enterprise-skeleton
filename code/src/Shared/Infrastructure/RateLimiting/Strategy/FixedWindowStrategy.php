<?php

declare(strict_types=1);

namespace Shared\Infrastructure\RateLimiting\Strategy;

use Shared\Infrastructure\RateLimiting\Storage\RateLimitStorageInterface;

/**
 * Fixed Window Rate Limiting Strategy.
 *
 * This strategy counts requests in fixed time windows (e.g. 60 seconds).
 * When a window expires, the counter resets to zero.
 */
final readonly class FixedWindowStrategy implements RateLimitStrategyInterface
{
    public function __construct(
        private RateLimitStorageInterface $storage,
    ) {
    }

    public function isLimitExceeded(string $key, string $resource, int $limit, int $windowSizeSeconds): bool
    {
        $count = $this->storage->increment($key, $resource, $windowSizeSeconds);

        return $count > $limit;
    }

    public function getLimitInfo(string $key, string $resource, int $limit, int $windowSizeSeconds): array
    {
        $count = $this->storage->get($key, $resource);
        $remaining = max(0, $limit - $count);

        $ttl = $this->storage->getTimeToLive($key, $resource);

        if ($ttl <= 0 && $count > 0) {
            $this->storage->reset($key, $resource);
            $count = 0;
            $remaining = $limit;
            $ttl = $windowSizeSeconds;
        }

        $resetTimestamp = time() + $ttl;

        return [
            'limit' => $limit,
            'remaining' => $remaining,
            'reset' => $resetTimestamp,
            'window_size' => $windowSizeSeconds,
        ];
    }
}
