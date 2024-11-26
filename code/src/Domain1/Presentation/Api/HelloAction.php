<?php

declare(strict_types=1);

namespace App\Domain1\Presentation\Api;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Server(
    url: "/",
    description: "API Server"
)]
final class HelloAction extends AbstractApiAction
{
    #[OA\Get(
        path: "/hello",
        summary: "Get hello message",
        tags: ["Hello"]
    )]
    #[OA\Response(
        response: 200,
        description: "Success",
        content: new OA\JsonContent(type: "string", example: "Test")
    )]
    #[Route('/hello', name: 'hello', methods: ['GET'])]
    public function __invoke(): Response
    {
        return new JsonResponse('Test');
    }
}
