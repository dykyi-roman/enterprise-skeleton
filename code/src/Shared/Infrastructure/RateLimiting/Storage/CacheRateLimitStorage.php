<?php

declare(strict_types=1);

namespace Shared\Infrastructure\RateLimiting\Storage;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Shared\Infrastructure\RateLimiting\Dto\RateLimitState;

/**
 * PSR-6 Cache adapter for rate limit storage.
 */
final readonly class CacheRateLimitStorage implements RateLimitStorageInterface
{
    public function __construct(
        private CacheItemPoolInterface $cache,
        private LoggerInterface $logger,
    ) {
    }

    public function increment(string $key, string $resource, int $windowSizeSeconds): int
    {
        try {
            $cacheKey = $this->getCacheKey($key, $resource);
            $item = $this->cache->getItem($cacheKey);

            if (!$item->isHit()) {
                $state = RateLimitState::createInitial($windowSizeSeconds);
                $item->set($state);
                $item->expiresAfter($windowSizeSeconds * 2); // Set X2 to be safe
                $this->cache->save($item);

                return 1;
            }

            /** @var RateLimitState $state */
            $state = $item->get();

            if ($state->isWindowExpired()) {
                $state = RateLimitState::createInitial($windowSizeSeconds);
            } else {
                $state = $state->withIncrementedCount();
                $state = $state->withWindowSize($windowSizeSeconds);
            }

            // Check for abnormally high values
            if ($state->count > 1000) {
                $this->logger->warning('Resetting abnormally high counter value', [
                    'cacheKey' => $cacheKey,
                    'oldValue' => $state->count,
                ]);
                $state = RateLimitState::createInitial($windowSizeSeconds);
            }

            $item->set($state);
            $item->expiresAfter($windowSizeSeconds * 2);
            $this->cache->save($item);

            return $state->count;
        } catch (\InvalidArgumentException $e) {
            $this->logger->error('Error incrementing rate limit counter', [
                'key' => $key,
                'resource' => $resource,
                'exception' => $e->getMessage(),
            ]);

            return 1;
        }
    }

    public function get(string $key, string $resource): int
    {
        try {
            $cacheKey = $this->getCacheKey($key, $resource);
            $item = $this->cache->getItem($cacheKey);

            if (!$item->isHit()) {
                return 0;
            }

            /** @var RateLimitState $state */
            $state = $item->get();

            if ($state->isWindowExpired()) {
                return 0;
            }

            return $state->count;
        } catch (\InvalidArgumentException $e) {
            $this->logger->error('Error getting rate limit counter', [
                'key' => $key,
                'resource' => $resource,
                'exception' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    public function getTimeToLive(string $key, string $resource): int
    {
        try {
            $cacheKey = $this->getCacheKey($key, $resource);
            $item = $this->cache->getItem($cacheKey);

            if (!$item->isHit()) {
                return 0;
            }

            /** @var RateLimitState $state */
            $state = $item->get();

            return $state->getRemainingTime();
        } catch (\InvalidArgumentException $e) {
            $this->logger->error('Error getting time to live', [
                'key' => $key,
                'resource' => $resource,
                'exception' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    public function reset(string $key, string $resource): bool
    {
        try {
            $cacheKey = $this->getCacheKey($key, $resource);

            return $this->cache->deleteItem($cacheKey);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error('Error resetting rate limit counter', [
                'key' => $key,
                'resource' => $resource,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function getCacheKey(string $key, string $resource): string
    {
        return 'rate_limit_'.md5($key.$resource);
    }
}
