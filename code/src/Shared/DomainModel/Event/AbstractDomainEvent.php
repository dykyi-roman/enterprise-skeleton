<?php

declare(strict_types=1);

namespace Shared\DomainModel\Event;

abstract readonly class AbstractDomainEvent implements DomainEventInterface
{
    public function __construct(
        private string $eventId,
        private string $aggregateId,
        private \DateTimeImmutable $occurredAt,
    ) {
    }

    public function getEventId(): string
    {
        return $this->eventId;
    }

    public function getAggregateId(): string
    {
        return $this->aggregateId;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function getEventName(): string
    {
        return static::class;
    }

    public function jsonSerialize(): array
    {
        return [
            'eventId' => $this->eventId,
            'occurredAt' => $this->occurredAt->format(\DateTimeImmutable::ATOM),
            'eventName' => $this->getEventName(),
        ];
    }
}
