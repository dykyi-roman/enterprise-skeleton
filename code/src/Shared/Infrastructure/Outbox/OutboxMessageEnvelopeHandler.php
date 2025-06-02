<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Outbox;

use Shared\DomainModel\Event\DomainEventInterface;
use Shared\DomainModel\Service\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class OutboxMessageEnvelopeHandler
{
    public function __construct(
        private MessageBusInterface $eventBus,
        private SerializerInterface $serializer,
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
            throw new \RuntimeException(sprintf("Unknown or invalid event type: %s", $eventType));
        }
        
        /** @var DomainEventInterface $event */
        $event = $this->serializer->deserialize($payload, $eventType, 'json');
        
        $this->eventBus->dispatch($event);
    }
}
