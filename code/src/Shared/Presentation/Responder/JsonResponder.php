<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Responder;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class JsonResponder extends AbstractResponder
{
    public function handle(Request $request, \Closure $next)
    {
        try {
            if (!$this->supportsContentType($request->getAcceptableContentTypes())) {
                return throw new \RuntimeException('Unsupported content type');
            }

            $response = $next($request);
            if (!$response instanceof Response) {
                return $response;
            }

            if (!$response->original instanceof ResponderInterface) {
                return $response;
            }

            return $this->createResponse($response->original);
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'message' => $exception->getMessage()
                ]
            ], 500);
        }
    }

    protected function supportsContentType(array $contentTypes): bool
    {
        if (empty($contentTypes)) {
            return false;
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
