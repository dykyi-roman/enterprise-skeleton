<?php

declare(strict_types=1);

namespace CoreDomain\Presentation\Api\Action;

use OpenApi\Attributes as OA;
use Shared\Infrastructure\RateLimiting\Attribute\RateLimit;
use Shared\Presentation\Api\AbstractApiAction;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for testing rate limiting functionality
 */
final class RateLimitTestController extends AbstractApiAction
{
    /**
     * Test endpoint with strict rate limits - only 3 requests per 10 seconds
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
     * Test endpoint with moderate rate limits - 10 requests per 30 seconds
     *
     * @return Response JSON response with timestamp and request count data
     */
    #[Route('/api/test/rate-limit/moderate', name: 'api_test_rate_limit_moderate', methods: ['GET'])]
    #[RateLimit(limit: 1, windowSizeSeconds: 30)]
    public function moderateLimitTest(): Response
    {
        return new JsonResponse([
            'timestamp' => time(),
            'message' => 'Moderate rate limit test passed! (10 requests / 30 seconds)',
        ]);
    }

    /**
     * Test endpoint with no specific rate limit - will use default if configured globally
     *
     * @return Response JSON response with timestamp
     */
    #[Route('/api/test/rate-limit/none', name: 'api_test_rate_limit_none', methods: ['GET'])]
    #[OA\Get(
        path: '/api/test/rate-limit/none', 
        summary: 'Test endpoint without specific rate limiting',
        description: 'This endpoint has no specific rate limits configured',
        tags: ['Rate Limiting Test'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response with timestamp',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'timestamp', type: 'integer'),
                        new OA\Property(property: 'message', type: 'string'),
                    ]
                )
            )
        ]
    )]
    public function noLimitTest(): Response
    {
        return new JsonResponse([
            'timestamp' => time(),
            'message' => 'No specific rate limit configured for this endpoint',
        ]);
    }
}
