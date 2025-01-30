<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Responder;

use Symfony\Component\HttpFoundation\JsonResponse;

final class JsonResponder extends AbstractResponder
{
    /** @param array<string> $contentTypes */
    protected function supportsContentType(array $contentTypes): bool
    {
        return in_array('application/json', $contentTypes, true);
    }

    protected function createResponse(ResponderInterface $result): JsonResponse
    {
        return new JsonResponse($result->payload(), $result->statusCode());
    }
}
