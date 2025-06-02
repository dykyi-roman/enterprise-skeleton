<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Outbox\Publisher;

use Shared\DomainModel\Event\DomainEventInterface;

interface OutboxPublisherInterface
{
    /**
     * @throws \RuntimeException
     */
    public function publish(DomainEventInterface $event): void;
}
