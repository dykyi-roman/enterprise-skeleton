<?php

declare(strict_types=1);

namespace Shared\DomainModel\Service;

use Shared\DomainModel\Event\DomainEventInterface;

interface EventStoreInterface
{
    /**
     * @throws \RuntimeException
     */
    public function append(DomainEventInterface $event): void;

    /**
     * @return array<DomainEventInterface>
     *
     * @throws \RuntimeException
     */
    public function getEventsForAggregate(mixed $aggregateId): array;

    /**
     * @return array<DomainEventInterface>
     *
     * @throws \RuntimeException
     */
    public function getEventsByType(string $eventType): array;
}
