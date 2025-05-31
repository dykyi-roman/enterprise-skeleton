<?php

declare(strict_types=1);

namespace CoreDomain\DomainModel\Entity;

use Shared\DomainModel\Entity\AggregateRoot;
use Shared\DomainModel\Event\DomainEventInterface;

/**
 * @implements AggregateRoot<DomainEventInterface>
 */
abstract class AbstractAggregateRoot implements AggregateRoot
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
