<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Outbox\Publisher;

use Shared\DomainModel\Event\DomainEventInterface;
use Shared\Infrastructure\Outbox\Repository\OutboxEventRepository;
use Shared\Infrastructure\Outbox\ValueObject\OutboxEvent;

final readonly class OutboxPublisher implements OutboxPublisherInterface
{
    public function __construct(
        private OutboxEventRepository $outboxRepository,
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
        } catch (\Throwable $exception) {
            throw new \RuntimeException(sprintf('Error publishing event to outbox: %s', $exception->getMessage()), 0, $exception);
        }
    }
}
