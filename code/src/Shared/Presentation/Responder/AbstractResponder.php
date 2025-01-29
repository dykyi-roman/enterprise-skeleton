<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Responder;

abstract class AbstractResponder
{
    protected function beforeResponse($response)
    {
        return $response;
    }

    abstract protected function supportsContentType(array $contentTypes): bool;

    abstract protected function createResponse(ResponderInterface $result);
}
