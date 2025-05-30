<?php

declare(strict_types=1);

namespace Shared\Infrastructure\ErrorHandler;

use Psr\Log\LoggerInterface;
use Shared\Application\Exception\ApplicationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final readonly class GlobalErrorHandler implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if (!$this->supportsContentType($request->getAcceptableContentTypes())) {
            return;
        }

        $exception = $event->getThrowable();
        $statusCode = $this->resolveStatusCode($exception);

        $errorResponse = $this->createErrorResponse($exception);

        // Log error for server-side errors
        if ($statusCode >= Response::HTTP_INTERNAL_SERVER_ERROR) {
            $this->logger->error($exception->getMessage(), [
                'exception' => $exception,
                'trace' => $exception->getTraceAsString(),
            ]);
        }

        $response = new JsonResponse($errorResponse, $statusCode);
        $event->setResponse($response);
    }

    /** @param array<string> $contentTypes */
    private function supportsContentType(array $contentTypes): bool
    {
        return in_array('application/json', $contentTypes, true);
    }

    private function resolveStatusCode(\Throwable $exception): int
    {
        return match (true) {
            $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
            $exception instanceof ValidationFailedException => Response::HTTP_BAD_REQUEST,
            $exception instanceof \DomainException => Response::HTTP_UNPROCESSABLE_ENTITY,
            $exception instanceof \InvalidArgumentException => Response::HTTP_BAD_REQUEST,
            $exception instanceof \LogicException => Response::HTTP_CONFLICT,
            default => Response::HTTP_INTERNAL_SERVER_ERROR,
        };
    }

    /**
     * @return array{
     *     success: false,
     *     error: array{
     *         type: string,
     *         message: string,
     *         code: string,
     *         useCase?: string,
     *         violations?: array<array{property: string, message: string, code: string|null}>
     *     }
     * }
     */
    private function createErrorResponse(\Throwable $exception): array
    {
        $errorResponse = [
            'success' => false,
            'error' => [
                'type' => $this->getErrorType($exception),
                'message' => $exception->getMessage(),
                'code' => $this->getErrorCode($exception),
            ],
        ];

        // Add validation errors if present
        if ($exception instanceof ValidationFailedException) {
            $errorResponse['error']['violations'] = $this->getValidationErrors($exception);
        }

        // Add use case name if present
        if ($exception instanceof ApplicationException) {
            $errorResponse['error']['useCase'] = $exception->useCaseName;
        }

        return $errorResponse;
    }

    private function getErrorType(\Throwable $exception): string
    {
        return match (true) {
            $exception instanceof ValidationFailedException => 'validation_error',
            $exception instanceof \DomainException => 'domain_error',
            $exception instanceof HttpExceptionInterface => 'http_error',
            $exception instanceof \RuntimeException => 'runtime_error',
            $exception instanceof \LogicException => 'logic_error',
            default => 'system_error',
        };
    }

    private function getErrorCode(\Throwable $exception): string
    {
        // Convert exception class name to snake case error code
        $className = (new \ReflectionClass($exception))->getShortName();

        $snakeCase = preg_replace('/(?<!^)[A-Z]/', '_$0', $className);

        return strtolower($snakeCase ?? $className);
    }

    /**
     * @return array<array{property: string, message: string, code: string|null}>
     */
    private function getValidationErrors(ValidationFailedException $exception): array
    {
        $violations = [];
        foreach ($exception->getViolations() as $violation) {
            $violations[] = [
                'property' => $violation->getPropertyPath(),
                'message' => (string) $violation->getMessage(),
                'code' => $violation->getCode(),
            ];
        }

        return $violations;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 0],
        ];
    }
}
