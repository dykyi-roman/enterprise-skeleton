<?php

declare(strict_types=1);

namespace Shared\Infrastructure\RateLimiting\Storage;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Implementation of rate limit storage using PSR-6 Cache
 */
final readonly class CacheRateLimitStorage implements RateLimitStorageInterface
{
    private const int DEFAULT_WINDOW_SIZE_SECONDS = 60;

    /**
     * @param CacheItemPoolInterface $cache PSR-6 cache implementation
     * @param LoggerInterface $logger PSR-3 logger implementation
     */
    public function __construct(
        private CacheItemPoolInterface $cache,
        private LoggerInterface $logger = new NullLogger(),
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function increment(string $key, string $resource, int $windowSizeSeconds): int
    {
        try {
            $cacheKey = $this->getCacheKey($key, $resource);
            $item = $this->cache->getItem($cacheKey);
            
            if (!$item->isHit()) {
                $value = 1;
                $item->set($value);
                $item->expiresAfter($windowSizeSeconds);
                $this->cache->save($item);
                return $value;
            }
            
            $value = (int) $item->get();
            $value++;
            $item->set($value);
            $this->cache->save($item);
            
            return $value;
        } catch (InvalidArgumentException $e) {
            $this->logger->error('Error incrementing rate limit counter', [
                'key' => $key,
                'resource' => $resource,
                'exception' => $e->getMessage(),
            ]);
            
            return 1;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, string $resource): int
    {
        try {
            $cacheKey = $this->getCacheKey($key, $resource);
            $item = $this->cache->getItem($cacheKey);
            
            if (!$item->isHit()) {
                return 0;
            }
            
            return (int) $item->get();
        } catch (InvalidArgumentException $e) {
            $this->logger->error('Error getting rate limit counter', [
                'key' => $key,
                'resource' => $resource,
                'exception' => $e->getMessage(),
            ]);
            
            return 0;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeToLive(string $key, string $resource): int
    {
        try {
            $cacheKey = $this->getCacheKey($key, $resource);
            $item = $this->cache->getItem($cacheKey);
            
            if (!$item->isHit()) {
                return 0;
            }
            
            if (method_exists($this->cache, 'getTtl')) {
                return (int) $this->cache->getTtl($cacheKey);
            }
            
            if (method_exists($item, 'getMetadata') && is_array($item->getMetadata())) {
                $expirationTimestamp = $item->getMetadata()['expiry'] ?? null;
                
                if ($expirationTimestamp !== null) {
                    $ttl = $expirationTimestamp - time();
                    return $ttl > 0 ? $ttl : 0;
                }
            }
            
            $this->logger->warning('Unable to determine TTL for cache item, returning default value', [
                'key' => $key,
                'resource' => $resource,
            ]);
            
            return self::DEFAULT_WINDOW_SIZE_SECONDS;
        } catch (InvalidArgumentException $e) {
            $this->logger->error('Error getting time to live', [
                'key' => $key,
                'resource' => $resource,
                'exception' => $e->getMessage(),
            ]);
            
            return 0;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reset(string $key, string $resource): bool
    {
        try {
            $cacheKey = $this->getCacheKey($key, $resource);
            return $this->cache->deleteItem($cacheKey);
        } catch (InvalidArgumentException $e) {
            $this->logger->error('Error resetting rate limit counter', [
                'key' => $key,
                'resource' => $resource,
                'exception' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function resetAll(string $resource): bool
    {
        // For PSR-6, there is no standard way to delete items by pattern
        // If such functionality is needed, consider using a specific cache implementation
        // that supports tags or deletion patterns
        $this->logger->warning('resetAll called but may not be fully effective with standard PSR-6', [
            'resource' => $resource,
        ]);
        
        try {
            // Clearing the entire cache is not an optimal solution, but it may be the only one
            // for standard PSR-6 without extensions
            if (method_exists($this->cache, 'clear')) {
                // WARNING: this will clear ALL cache items, not just those for the given resource
                // return $this->cache->clear();
                
                // Safely return true, but log a warning
                $this->logger->warning('resetAll is not implemented for pure PSR-6 Cache - would require clearing entire cache', [
                    'resource' => $resource,
                ]);
                return true;
            }
            
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error in resetAll', [
                'resource' => $resource,
                'exception' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
    
    /**
     * Generates a cache key from the request key and resource
     */
    private function getCacheKey(string $key, string $resource): string
    {
        return 'rate_limit_' . md5($resource . '_' . $key);
    }
}