<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Responder;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class JsonResponder extends AbstractResponder
{
    public function handle(Request $request, \Closure $next)
    {
        $response = $next($request);

        if (!$response instanceof ResponderInterface) {
            return $response;
        }

        if (!$this->supportsContentType($request->getAcceptable())) {
            return $response;
        }

        return $this->createResponse($response);
    }

    protected function supportsContentType(array $contentTypes): bool
    {
        if (empty($contentTypes)) {
            return true;
        }

        return in_array('application/json', $contentTypes, true);
    }

    protected function createResponse(ResponderInterface $result): JsonResponse
    {
        return response()->json(
            $result->payload(),
            $result->statusCode()
        );
    }
}
