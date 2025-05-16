<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Responder;

use Symfony\Component\HttpFoundation\Response;

final class HtmlResponder extends AbstractResponder
{
    /** @param array<string> $contentTypes */
    #[\Override]
    protected function supportsContentType(array $contentTypes): bool
    {
        return in_array('text/html', $contentTypes, true);
    }

    #[\Override]
    protected function createResponse(ResponderInterface $result): Response
    {
        if ($result instanceof TemplateResponderInterface) {
            $content = $result->template();
            /** @var mixed $value */
            foreach ($result->payload() as $key => $value) {
                $content = str_replace('{{'.$key.'}}', null === $value ? '' : (string) $value, $content);
            }

            $response = new Response($content, $result->statusCode());
            foreach ($result->headers() as $key => $value) {
                $response->headers->set($key, $value);
            }

            return $response;
        }

        return new Response();
    }
}
