<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Responder;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class HtmlResponder extends AbstractResponder
{
    public function __construct(
        private readonly Factory $viewFactory,
    ) {
    }

    public function handle(Request $request, \Closure $next): View|Response
    {
        $response = $next($request);
        if (!$response instanceof Response) {
            return $response;
        }

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

    protected function createResponse(ResponderInterface $result): View
    {
        if (!$result instanceof TemplateResponderInterface) {
            throw new \InvalidArgumentException('Result must implement TemplateResponderInterface');
        }

        return $this->viewFactory->make($result->template(), $result->payload());
    }
}
