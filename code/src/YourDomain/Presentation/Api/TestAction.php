<?php

declare(strict_types=1);

namespace App\YourDomain\Presentation\Api;

use OpenApi\Attributes as OA;
use App\Providers\Attributes\ApiRoute;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

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
#[ApiRoute('/api/test', ['GET'], 'api.test')]
final class TestAction extends AbstractApiAction
{
    public function __invoke(): Response
    {
        return new JsonResponse(['message' => 'API Test Action']);
    }
}
