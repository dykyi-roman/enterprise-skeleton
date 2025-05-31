<?php

declare(strict_types=1);

namespace CoreDomain\DomainModel\Event;

use Ramsey\Uuid\Uuid;
use Shared\DomainModel\Event\DomainEventInterface;

abstract readonly class AbstractDomainEvent implements DomainEventInterface
{
    private string $eventId;
    private \DateTimeImmutable $occurredAt;

    public function __construct(
        ?string $eventId = null,
        ?\DateTimeImmutable $occurredAt = null,
    ) {
        $this->eventId = $eventId ?? Uuid::uuid4()->toString();
        $this->occurredAt = $occurredAt ?? new \DateTimeImmutable();
    }

    public function getEventId(): string
    {
        return $this->eventId;
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
