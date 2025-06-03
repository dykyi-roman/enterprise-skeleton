<?php

declare(strict_types=1);

namespace Shared\DomainModel\Event;

abstract readonly class AbstractDomainEvent implements DomainEventInterface
{
    public function __construct(
        private string $eventId,
        private string $aggregateId,
        private \DateTimeImmutable $occurredAt,
        private int $version = 1,
    ) {
    }

    public function getVersion(): int
    {
        return $this->version;
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
            'version' => $this->version,
            'event_id' => $this->eventId,
            'aggregate_id' => $this->aggregateId,
            'occurred_at' => $this->occurredAt->format(\DateTimeImmutable::ATOM),
            'event_name' => $this->getEventName(),
        ];
    }
}
