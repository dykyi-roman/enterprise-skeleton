<?php

declare(strict_types=1);

namespace Shared\Infrastructure\EventStore\Repository;

use Doctrine\DBAL\Connection;
use RuntimeException;
use Shared\DomainModel\Event\DomainEventInterface;
use Shared\Infrastructure\EventStore\EventStoreInterface;

final readonly class DoctrineEventStore implements EventStoreInterface
{
    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * @throws \RuntimeException
     */
    public function append(DomainEventInterface $event): void
    {
        try {
            $this->connection->insert('event_store', [
                'event_id' => $event->getEventId(),
                'occurred_on' => $event->getOccurredAt()->format('Y-m-d H:i:s.u'),
                'event_type' => $event->getEventName(),
                'aggregate_id' => $event->getAggregateId(),
                'event_data' => json_encode($event->jsonSerialize(), JSON_THROW_ON_ERROR),
            ]);
        } catch (\Throwable $e) {
            throw new RuntimeException(sprintf('Error saving event: %s', $e->getMessage()), 0, $e);
        }
    }

    /**
     * @return array<DomainEventInterface>
     * @throws \RuntimeException
     */
    public function getEventsForAggregate(mixed $aggregateId): array
    {
        try {
            $stmt = $this->connection->createQueryBuilder()
                ->select('*')
                ->from('event_store')
                ->where('aggregate_id = :aggregateId')
                ->orderBy('occurred_on', 'ASC')
                ->setParameter('aggregateId', $aggregateId->toString())
                ->executeQuery();

            $events = [];
            while ($row = $stmt->fetchAssociative()) {
                $events[] = $this->deserializeEvent($row);
            }

            return $events;
        } catch (\Throwable $e) {
            throw new RuntimeException(sprintf('Error getting events for aggregate: %s', $e->getMessage()), 0, $e);
        }
    }

    /**
     * @return array<DomainEventInterface>
     * @throws \RuntimeException
     */
    public function getEventsByType(string $eventType): array
    {
        try {
            $stmt = $this->connection->createQueryBuilder()
                ->select('*')
                ->from('event_store')
                ->where('event_type = :eventType')
                ->orderBy('occurred_on', 'ASC')
                ->setParameter('eventType', $eventType)
                ->executeQuery();

            $events = [];
            while ($row = $stmt->fetchAssociative()) {
                $events[] = $this->deserializeEvent($row);
            }

            return $events;
        } catch (\Throwable $e) {
            throw new RuntimeException(sprintf('Error getting events by type: %s', $e->getMessage()), 0, $e);
        }
    }

    /**
     * @param array<string, mixed> $row
     * @throws RuntimeException
     */
    private function deserializeEvent(array $row): DomainEventInterface
    {
        try {
            $eventType = $row['event_type'] ?? null;
            if (!$eventType || !class_exists($eventType)) {
                throw new \RuntimeException(sprintf('Unknown or invalid event type: %s', $eventType));
            }

            if (!is_subclass_of($eventType, DomainEventInterface::class)) {
                throw new \RuntimeException(
                    sprintf('Event type %s must implement %s', $eventType, DomainEventInterface::class)
                );
            }

            $data = json_decode($row['event_data'], true, 512, JSON_THROW_ON_ERROR);
            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new \RuntimeException(sprintf('Invalid JSON payload: %s', json_last_error_msg()));
            }

            return $row['event_type']::fromArray($data);
        } catch (\Throwable $e) {
            throw new RuntimeException(sprintf("Error while deserializing event: %s", $e->getMessage()), 0, $e);
        }
    }
}
