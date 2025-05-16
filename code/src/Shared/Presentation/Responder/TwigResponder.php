<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Responder;

use Symfony\Component\HttpFoundation\Response;

final class TwigResponder extends AbstractResponder
{
    public function __construct(
    ) {
    }

    /** @param array<string> $contentTypes */
    #[\Override]
    protected function supportsContentType(array $contentTypes): bool
    {
        return in_array('text/html', $contentTypes, true);
    }

    #[\Override]
    protected function createResponse(ResponderInterface $result): Response
    {
        // TODO:: Generate twig file logic here

        return new Response();
    }
}
