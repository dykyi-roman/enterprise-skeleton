<?php

declare(strict_types=1);

namespace App\YourDomain\Presentation\Api;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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
    public function __invoke(): Response
    {
        return new JsonResponse('Test');
    }
}
