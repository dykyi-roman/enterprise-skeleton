<?php

declare(strict_types=1);

namespace Shared\Infrastructure\RequestId\Middleware;

use Shared\DomainModel\Service\RequestIdServiceInterface;
use Shared\DomainModel\ValueObject\RequestId;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

final readonly class RequestIdMiddleware
{
    public const string REQUEST_ID_HEADER = 'X-Request-ID';
    public const string REQUEST_ID_ATTRIBUTE = '_request_id';

    public function __construct(
        private RequestIdServiceInterface $requestIdService,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $requestId = $this->extractOrGenerateRequestId($request);

        $this->requestIdService->setCurrentRequestId($requestId);
        $request->attributes->set(self::REQUEST_ID_ATTRIBUTE, $requestId);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$this->requestIdService->hasRequestId()) {
            return;
        }

        $response = $event->getResponse();
        $requestId = $this->requestIdService->getCurrentRequestId();

        $response->headers->set(self::REQUEST_ID_HEADER, $requestId->getValue());
    }

    private function extractOrGenerateRequestId(Request $request): RequestId
    {
        $requestIdValue = $request->headers->get(self::REQUEST_ID_HEADER);

        if (null !== $requestIdValue && $this->isValidRequestId($requestIdValue)) {
            return RequestId::fromString($requestIdValue);
        }

        return RequestId::generate();
    }

    private function isValidRequestId(string $value): bool
    {
        try {
            RequestId::fromString($value);

            return true;
        } catch (\InvalidArgumentException) {
            return false;
        }
    }
}
