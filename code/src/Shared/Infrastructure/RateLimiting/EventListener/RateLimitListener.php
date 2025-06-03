<?php

declare(strict_types=1);

namespace Shared\Infrastructure\RateLimiting\EventListener;

use Psr\Log\LoggerInterface;
use Shared\Infrastructure\RateLimiting\Attribute\RateLimit;
use Shared\Infrastructure\RateLimiting\Exception\RateLimitExceededException;
use Shared\Infrastructure\RateLimiting\Identifier\RequestIdentifierInterface;
use Shared\Infrastructure\RateLimiting\RateLimiterFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event listener for rate limiting based on controller attributes.
 */
final readonly class RateLimitListener implements EventSubscriberInterface
{
    public function __construct(
        private RateLimiterFactory $rateLimiterFactory,
        private RequestIdentifierInterface $identifier,
        private LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', 10], // High priority for early rejection of request
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            // Ignore sub-requests
            return;
        }

        $controller = $event->getController();

        if (is_array($controller) && 2 === count($controller)) {
            $controllerObject = $controller[0];
            $controllerMethod = $controller[1];

            try {
                $reflectionClass = new \ReflectionClass($controllerObject);
                $reflectionMethod = $reflectionClass->getMethod($controllerMethod);

                $methodAttributes = $reflectionMethod->getAttributes(RateLimit::class);
                if (!empty($methodAttributes)) {
                    $this->applyRateLimit($methodAttributes[0]->newInstance(), $reflectionMethod, $event);

                    return;
                }

                $classAttributes = $reflectionClass->getAttributes(RateLimit::class);
                if (!empty($classAttributes)) {
                    $this->applyRateLimit($classAttributes[0]->newInstance(), $reflectionClass, $event);
                }
            } catch (\ReflectionException $e) {
                $this->logger->warning('RateLimit reflection error: '.$e->getMessage(), [
                    'exception' => $e,
                    'controller' => get_class($controllerObject),
                    'method' => $controllerMethod,
                ]);
            }
        }
    }

    private function applyRateLimit(
        RateLimit $rateLimit,
        \ReflectionClass|\ReflectionMethod $reflection,
        ControllerEvent $event,
    ): void {
        $request = $event->getRequest();

        $key = $rateLimit->getKey() ?? $this->identifier->getIdentifier($request);

        $resource = $reflection instanceof \ReflectionMethod
            ? $reflection->getDeclaringClass()->getName().'::'.$reflection->getName()
            : $reflection->getName();

        $rateLimiter = $this->rateLimiterFactory->create(
            $resource,
            $rateLimit->getLimit(),
            $rateLimit->getWindowSizeSeconds()
        );

        try {
            $rateLimiter->check($key, $resource);

            $limitInfo = $rateLimiter->getLimitInfo($key, $resource);
            $response = $event->getRequest()->attributes->get('_rate_limit_headers', []);
            $response['X-RateLimit-Limit'] = $limitInfo['limit'];
            $response['X-RateLimit-Remaining'] = $limitInfo['remaining'];
            $response['X-RateLimit-Reset'] = $limitInfo['reset'];
            $event->getRequest()->attributes->set('_rate_limit_headers', $response);
        } catch (RateLimitExceededException $e) {
            $this->logger->info('Rate limit exceeded', [
                'resource' => $e->getResource(),
                'limit' => $e->getLimitValue(),
                'wait_time' => $e->getWaitTimeSeconds(),
                'ip' => $request->getClientIp(),
                'path' => $request->getPathInfo(),
            ]);

            $response = new JsonResponse(
                [
                    'success' => false,
                    'status' => 'error',
                    'error' => [
                        'code' => 101,
                        'message' => $e->getMessage(),
                        'wait_seconds' => $e->getWaitTimeSeconds(),
                    ],
                ],
                Response::HTTP_TOO_MANY_REQUESTS
            );

            $response->headers->set('X-RateLimit-Limit', (string) $e->getLimitValue());
            $response->headers->set('X-RateLimit-Remaining', '0');
            $response->headers->set('X-RateLimit-Reset', (string) (time() + $e->getWaitTimeSeconds()));
            $response->headers->set('Retry-After', (string) $e->getWaitTimeSeconds());

            $event->setController(function () use ($response) {
                return $response;
            });
        }
    }
}
