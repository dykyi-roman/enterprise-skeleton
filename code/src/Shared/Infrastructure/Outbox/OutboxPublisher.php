<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Outbox;

use RuntimeException;
use Shared\DomainModel\Event\DomainEventInterface;
use Throwable;

final readonly class OutboxPublisher implements OutboxPublisherInterface
{
    /**
     * @param OutboxEventRepository $outboxRepository
     */
    public function __construct(
        private OutboxEventRepository $outboxRepository
    ) {
    }

    /**
     * @throws \RuntimeException
     */
    public function publish(DomainEventInterface $event): void
    {
        try {
            $this->outboxRepository->save(
                OutboxEvent::create(
                    $event->getEventId(),
                    $event->getEventName(),
                    $event->getAggregateId(),
                    json_encode($event->jsonSerialize(), JSON_THROW_ON_ERROR),
                ),
            );
        } catch (Throwable $exception) {
            throw new RuntimeException(
                sprintf('Error publishing event to outbox: %s', $exception->getMessage()),
                0,
                $exception,
            );
        }
    }
}
