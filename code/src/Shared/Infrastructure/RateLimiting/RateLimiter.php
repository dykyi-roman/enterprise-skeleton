<?php

declare(strict_types=1);

namespace Shared\Infrastructure\RateLimiting;

use Shared\Infrastructure\RateLimiting\Configuration\RateLimitConfiguration;
use Shared\Infrastructure\RateLimiting\Exception\RateLimitExceededException;
use Shared\Infrastructure\RateLimiting\Strategy\RateLimitStrategyInterface;

/**
 * Main RateLimiter implementation, responsible for checking request limits.
 */
final readonly class RateLimiter implements RateLimiterInterface
{
    /**
     * @param RateLimitStrategyInterface $strategy      Rate limiting strategy
     * @param RateLimitConfiguration     $configuration Configuration with limits
     */
    public function __construct(
        private RateLimitStrategyInterface $strategy,
        private RateLimitConfiguration $configuration,
    ) {
    }

    /**
     * Checks if a request can be processed given the rate limits.
     *
     * @param string $key      Request identifier (IP, API key, etc.)
     * @param string $resource Resource identifier, optional parameter
     *
     * @throws RateLimitExceededException if the limit is exceeded
     */
    public function check(string $key, string $resource = ''): void
    {
        $actualResource = $resource ?: $this->configuration->getResource();
        $limit = $this->configuration->getLimit();
        $windowSize = $this->configuration->getWindowSizeSeconds();

        if ($this->strategy->isLimitExceeded($key, $actualResource, $limit, $windowSize)) {
            $limitInfo = $this->strategy->getLimitInfo($key, $actualResource, $limit, $windowSize);
            $waitTime = max(1, $limitInfo['reset'] - time());

            throw new RateLimitExceededException($actualResource, $limit, $waitTime, ['key' => $key, 'limit_info' => $limitInfo]);
        }
    }

    /**
     * Returns information about the current limit state.
     *
     * @param string $key      Request identifier
     * @param string $resource Resource identifier, optional parameter
     *
     * @return array<string,mixed> Limit information
     */
    public function getLimitInfo(string $key, string $resource = ''): array
    {
        $actualResource = $resource ?: $this->configuration->getResource();
        $limit = $this->configuration->getLimit();
        $windowSize = $this->configuration->getWindowSizeSeconds();

        return $this->strategy->getLimitInfo($key, $actualResource, $limit, $windowSize);
    }

    /**
     * Resets the counter for the specified key and resource.
     *
     * @param string $key      Request identifier
     * @param string $resource Resource identifier, optional parameter
     */
    public function reset(string $key, string $resource = ''): void
    {
        $actualResource = $resource ?: $this->configuration->getResource();
        // Reset should be implemented in the specific strategy using storage
        // But since the strategy doesn't have a reset method, we need to access storage directly
        // This is an architectural issue that needs to be addressed
    }
}
