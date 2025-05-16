<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Responder;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

abstract class AbstractResponder implements EventSubscriberInterface
{
    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onKernelView'],
        ];
    }

    /** @psalm-suppress PossiblyUnusedMethod */
    public function onKernelView(ViewEvent $viewEvent): void
    {
        $request = $viewEvent->getRequest();
        if (!$this->supportsContentType($request->getAcceptableContentTypes())) {
            return;
        }
        $result = $viewEvent->getControllerResult();

        if (!$result instanceof ResponderInterface) {
            return;
        }

        $viewEvent->setResponse($this->createResponse($result));
    }

    /** @param array<string> $contentTypes */
    abstract protected function supportsContentType(array $contentTypes): bool;

    abstract protected function createResponse(ResponderInterface $result): Response;
}
