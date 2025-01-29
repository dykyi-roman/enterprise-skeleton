<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Responder;

final class JsonResponder extends AbstractResponder
{
    public function handle($request, \Closure $next)
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
        return in_array('application/json', $contentTypes, true);
    }

    protected function createResponse(ResponderInterface $result)
    {
        return response()->json(
            $result->payload(),
            $result->statusCode()
        );
    }
}
