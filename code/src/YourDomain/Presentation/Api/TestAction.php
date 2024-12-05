<?php

declare(strict_types=1);

namespace App\CoreDomain\Presentation\Api;

use App\CoreDomain\Resources\Attribute\ApiRoute;
use OpenApi\Attributes as OA;
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
        return new JsonResponse('Test');
    }
}
