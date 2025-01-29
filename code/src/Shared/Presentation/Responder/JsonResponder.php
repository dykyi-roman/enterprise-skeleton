<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Responder;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

final class JsonResponder extends AbstractResponder
{
    public function handle(Request $request, \Closure $next)
    {
        try {
            $response = $next($request);

            if ($response instanceof \Throwable) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'message' => $response->getMessage()
                    ]
                ], 500);
            }

            if (!$response instanceof ResponderInterface) {
                return $response;
            }

            return response()->json(
                $response->payload(),
                $response->statusCode()
            );
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'message' => $e->getMessage()
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
