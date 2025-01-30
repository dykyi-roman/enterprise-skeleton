<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Responder;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

abstract class AbstractResponder
{
    abstract protected function handle(Request $request, \Closure $next): Response|JsonResponse;

    /**
     * @param string[] $contentTypes
     */
    abstract protected function supportsContentType(array $contentTypes): bool;

    abstract protected function createResponse(ResponderInterface $result): mixed;
}
