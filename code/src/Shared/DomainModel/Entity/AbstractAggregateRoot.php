<?php

declare(strict_types=1);

namespace Shared\DomainModel\Entity;

use Shared\DomainModel\Event\DomainEventInterface;

/**
 * @implements AggregateRootInterface<DomainEventInterface>
 */
abstract class AbstractAggregateRoot implements AggregateRootInterface
{
    /** @var DomainEventInterface[] */
    private array $domainEvents = [];

    public function recordEvent(DomainEventInterface $event): void
    {
        $this->domainEvents[] = $event;
    }

    /**
     * @return DomainEventInterface[]
     */
    public function releaseEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }
}
