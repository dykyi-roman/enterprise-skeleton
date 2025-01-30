<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Responder;

use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class HtmlResponder extends AbstractResponder
{
    public function __construct(
        private readonly Factory $viewFactory,
    ) {
    }

    public function handle(Request $request, \Closure $next): Response
    {
        $response = $next($request);
        if (!$response->original instanceof TemplateResponderInterface) {
            return $response;
        }

        return $this->createResponse($response->original);
    }

    /**
     * @param string[] $contentTypes
     */
    protected function supportsContentType(array $contentTypes): bool
    {
        if (empty($contentTypes)) {
            return false;
        }

        return in_array('text/html', $contentTypes, true);
    }

    protected function createResponse(ResponderInterface $result): Response
    {
        if (!$result instanceof TemplateResponderInterface) {
            throw new \InvalidArgumentException('Result must implement TemplateResponderInterface');
        }

        $view = $this->viewFactory->make($result->template(), $result->payload());

        $statusCode = max(100, min(599, $result->statusCode()));

        return response($view, $statusCode, $result->headers())
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('X-Frame-Options', 'DENY')
            ->header('X-XSS-Protection', '1; mode=block');
    }
}
