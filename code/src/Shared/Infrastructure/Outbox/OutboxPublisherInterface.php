<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Outbox;

use Shared\DomainModel\Event\DomainEventInterface;

interface OutboxPublisherInterface
{
    /**
     * @throws \RuntimeException
     */
    public function publish(DomainEventInterface $event): void;
}
