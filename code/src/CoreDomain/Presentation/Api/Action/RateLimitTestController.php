<?php

declare(strict_types=1);

namespace CoreDomain\Presentation\Api\Action;

use Shared\Infrastructure\Outbox\Publisher\OutboxPublisherInterface;
use Shared\Infrastructure\RateLimiting\Attribute\RateLimit;
use Shared\Presentation\Api\AbstractApiAction;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class RateLimitTestController extends AbstractApiAction
{
    public function __construct(
        private readonly OutboxPublisherInterface $outboxPublisher,
    ) {
    }

    /**
     * Test endpoint with strict rate limits - only 3 requests per 10 seconds.
     *
     * @return Response JSON response with timestamp and request count data
     */
    #[Route('/api/test/rate-limit/strict', name: 'api_test_rate_limit_strict', methods: ['GET'])]
    #[RateLimit(limit: 3, windowSizeSeconds: 10)]
    public function strictLimitTest(): Response
    {
        return new JsonResponse([
            'timestamp' => time(),
            'message' => 'Strict rate limit test passed! (3 requests / 10 seconds)',
        ]);
    }

    /**
     * Test endpoint with no specific rate limit - will use default if configured globally.
     *
     * @return Response JSON response with timestamp
     */
    #[Route('/api/test/rate-limit/none', name: 'api_test_rate_limit_none', methods: ['GET'])]
    public function noLimitTest(): Response
    {
        return new JsonResponse([
            'timestamp' => time(),
            'message' => 'No specific rate limit configured for this endpoint',
        ]);
    }
}
