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
            $response = $next($request);
            if (!$response instanceof Response) {
                return $response;
            }

            if (!$response->original instanceof ResponderInterface) {
                return $response;
            }

            if ($response instanceof \Throwable) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'message' => $response->getMessage()
                    ]
                ], 500);
            }

            // Handle wrapped responder from PreventEarlyResponseConversion middleware
            if (is_object($response) && method_exists($response, 'getResponder')) {
                $response = $response->getResponder();
            }


            return response()->json(
                $response->original->payload(),
                $response->original->statusCode(),
            );
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
