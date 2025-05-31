<?php

declare(strict_types=1);

namespace Shared\DomainModel\Entity;

use Shared\DomainModel\Event\DomainEventInterface;

/**
 * @template T of DomainEventInterface
 */
interface AggregateRoot
{
    /**
     * @param T $event
     */
    public function recordEvent(DomainEventInterface $event): void;

    /**
     * @return T[]
     */
    public function releaseEvents(): array;
}
