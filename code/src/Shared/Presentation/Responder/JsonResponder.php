<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Responder;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class JsonResponder extends AbstractResponder
{
    public function handle(Request $request, \Closure $next): JsonResponse
    {
        try {
            if (!$this->supportsContentType($request->getAcceptableContentTypes())) {
                throw new \RuntimeException('Unsupported content type');
            }

            $response = $next($request);
            if ($response instanceof JsonResponse) {
                return $response;
            }

            if (!$response instanceof Response || !$response->original instanceof ResponderInterface) {
                return response()->json($response);
            }

            return $this->createResponse($response->original);
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'message' => $exception->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * @param string[] $contentTypes
     */
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
            $result->statusCode(),
            $result->headers(),
        );
    }
}
