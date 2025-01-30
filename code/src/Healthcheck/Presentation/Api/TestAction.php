<?php

declare(strict_types=1);

namespace App\Healthcheck\Presentation\Api;

use App\CoreDomain\Resources\Attribute\ApiRoute;
use App\Healthcheck\Presentation\Api\Response\TestJsonResponder;
use App\Shared\Presentation\Api\AbstractApiAction;
use App\Shared\Presentation\Responder\ResponderInterface;
use OpenApi\Attributes as OA;

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
    public function __invoke(
        TestJsonResponder $responder,
    ): ResponderInterface {
        try {
            return $responder->success('Success!')->respond();
        } catch (\Throwable $exception) {
            return $responder->error($exception->getMessage())->respond();
        }
    }
}
