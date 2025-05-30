<?php

declare(strict_types=1);

namespace Healthcheck\Presentation\Api;

use Healthcheck\Presentation\Api\Resonse\TestJsonResponse;
use Shared\Presentation\Api\AbstractApiAction;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Annotation\Route;

final class TestAction extends AbstractApiAction
{
    #[OA\Get(
        path: '/api/test',
        summary: 'Test route message',
        tags: ['Test']
    )]
    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(type: 'string', example: 'Test')
    )]
    #[Route('/test', name: 'api_test', methods: ['GET'])]
    public function __invoke(
        TestJsonResponse $response,
    ): TestJsonResponse {
        try {
            return $response->success('Success!')->respond();
        } catch (\Throwable $exception) {
            return $response->error($exception->getMessage())->respond();
        }
    }
}
