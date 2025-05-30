<?php

declare(strict_types=1);

namespace Shared\Infrastructure\HttpClient;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

final readonly class HttpClientLoggingMiddleware
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(): callable
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                $this->logRequest($request);

                return $handler($request, $options)->then(
                    function (ResponseInterface $response) use ($request) {
                        $this->logResponse($request, $response);

                        return $response;
                    }
                );
            };
        };
    }

    private function logRequest(RequestInterface $request): void
    {
        $this->logger->info('HTTP Request', [
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri(),
            'headers' => $request->getHeaders(),
            'body' => (string) $request->getBody(),
        ]);
    }

    private function logResponse(RequestInterface $request, ResponseInterface $response): void
    {
        $this->logger->info('HTTP Response', [
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri(),
            'status_code' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => (string) $response->getBody(),
        ]);

        $response->getBody()->rewind();
    }
}
