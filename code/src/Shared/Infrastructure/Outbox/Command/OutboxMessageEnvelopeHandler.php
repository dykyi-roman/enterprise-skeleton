<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Outbox\Command;

use Shared\DomainModel\Event\DomainEventInterface;
use Shared\DomainModel\Service\MessageBusInterface;

final readonly class OutboxMessageEnvelopeHandler
{
    public function __construct(
        private MessageBusInterface $eventBus,
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function __invoke(OutboxMessageEnvelope $envelope): void
    {
        $payload = $envelope->getPayload();
        $metadata = $envelope->getMetadata();

        $eventType = $metadata['type'] ?? null;

        if (!$eventType || !class_exists($eventType)) {
            throw new \RuntimeException(sprintf('Unknown or invalid event type: %s', $eventType));
        }

        if (!is_subclass_of($eventType, DomainEventInterface::class)) {
            throw new \RuntimeException(sprintf('Event type %s must implement %s', $eventType, DomainEventInterface::class));
        }

        $data = json_decode($payload, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \RuntimeException(sprintf('Invalid JSON payload: %s', json_last_error_msg()));
        }

        /** @var DomainEventInterface $event */
        $event = $eventType::fromArray($data);

        $this->eventBus->dispatch($event);
    }
}
