<?php

declare(strict_types=1);

namespace Shared\Infrastructure\EventStore\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Shared\DomainModel\Event\DomainEventInterface;
use Shared\DomainModel\Service\EventStoreInterface;

final readonly class DoctrineEventStore implements EventStoreInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * @throws \RuntimeException
     */
    public function append(DomainEventInterface $event): void
    {
        try {
            $this->connection->insert(
                'event_store',
                [
                    'id' => uuid_create(),
                    'version' => $event->getVersion(),
                    'event_id' => $event->getEventId(),
                    'event_type' => $event->getEventName(),
                    'aggregate_id' => $event->getAggregateId(),
                    'occurred_at' => $event->getOccurredAt()->format('Y-m-d H:i:s.u'),
                    'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s.u'),
                    'payload' => json_encode($event->jsonSerialize(), JSON_THROW_ON_ERROR),
                ],
                [
                    'id' => Types::STRING,
                    'version' => Types::INTEGER,
                    'event_id' => Types::STRING,
                    'event_type' => Types::STRING,
                    'aggregate_id' => Types::STRING,
                    'occurred_at' => Types::STRING,
                    'created_at' => Types::STRING,
                    'payload' => Types::TEXT,
                ]
            );
        } catch (\Throwable $exception) {
            throw new \RuntimeException(sprintf('Error saving event: %s', $exception->getMessage()), 0, $exception);
        }
    }

    /**
     * @return array<DomainEventInterface>
     *
     * @throws \RuntimeException
     */
    public function getEventsForAggregate(mixed $aggregateId): array
    {
        try {
            $id = match (true) {
                is_object($aggregateId) && method_exists($aggregateId, 'toString') => $aggregateId->toString(),
                is_string($aggregateId) => $aggregateId,
                is_int($aggregateId) => (string) $aggregateId,
                default => throw new \InvalidArgumentException('Invalid aggregate ID type'),
            };

            $stmt = $this->connection->executeQuery(
                'SELECT * FROM event_store WHERE aggregate_id = :aggregateId ORDER BY occurred_at ASC',
                [
                    'aggregateId' => $id,
                ],
                [
                    'aggregateId' => Types::STRING,
                ]
            );

            $events = [];
            while ($row = $stmt->fetchAssociative()) {
                $events[] = $this->deserializeEvent($row);
            }

            return $events;
        } catch (\Throwable $exception) {
            throw new \RuntimeException(sprintf('Error getting events for aggregate: %s', $exception->getMessage()), 0, $exception);
        }
    }

    /**
     * @return array<DomainEventInterface>
     *
     * @throws \RuntimeException
     */
    public function getEventsByType(string $eventType): array
    {
        try {
            $stmt = $this->connection->executeQuery(
                'SELECT * FROM event_store WHERE event_type = :eventType ORDER BY occurred_at ASC',
                [
                    'eventType' => $eventType,
                ],
                [
                    'eventType' => Types::STRING,
                ]
            );

            $events = [];
            while ($row = $stmt->fetchAssociative()) {
                $events[] = $this->deserializeEvent($row);
            }

            return $events;
        } catch (\Throwable $exception) {
            throw new \RuntimeException(sprintf('Error getting events by type: %s', $exception->getMessage()), 0, $exception);
        }
    }

    /**
     * @param array<string, mixed> $row
     *
     * @throws \RuntimeException
     */
    private function deserializeEvent(array $row): DomainEventInterface
    {
        try {
            $eventType = $row['event_type'] ?? null;
            if (!is_string($eventType) || !class_exists($eventType)) {
                throw new \RuntimeException(sprintf('Unknown or invalid event type: %s', is_scalar($eventType) ? (string) $eventType : get_debug_type($eventType)));
            }

            if (!is_subclass_of($eventType, DomainEventInterface::class)) {
                throw new \RuntimeException(sprintf('Event type %s must implement interface %s', $eventType, DomainEventInterface::class));
            }

            $payload = $row['payload'] ?? null;
            if (!is_string($payload) || '' === $payload) {
                throw new \RuntimeException('Payload missing in event record');
            }

            if (str_starts_with($payload, '"') && str_ends_with($payload, '"')) {
                $decoded = json_decode($payload, false, 512, JSON_THROW_ON_ERROR);
                $payload = is_string($decoded) ? $decoded : $payload;
            }

            try {
                $decodedData = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
                if (!is_array($decodedData)) {
                    throw new \RuntimeException('Event payload must be an array');
                }
                /** @var array<string, mixed> $data */
                $data = $decodedData;
            } catch (\Throwable) {
                $data = [
                    'event_id' => isset($row['event_id']) && is_scalar($row['event_id']) ? (string) $row['event_id'] : '',
                    'occurred_at' => isset($row['occurred_at']) && is_scalar($row['occurred_at']) ? (string) $row['occurred_at'] : '',
                    'aggregate_id' => isset($row['aggregate_id']) && is_scalar($row['aggregate_id']) ? (string) $row['aggregate_id'] : '',
                ];
            }

            if (!isset($data['event_id']) && isset($row['event_id']) && is_scalar($row['event_id'])) {
                $data['event_id'] = (string) $row['event_id'];
            }

            if (!isset($data['occurred_at']) && isset($row['occurred_at']) && is_scalar($row['occurred_at'])) {
                $data['occurred_at'] = (string) $row['occurred_at'];
            }

            if (!isset($data['aggregate_id']) && isset($row['aggregate_id']) && is_scalar($row['aggregate_id'])) {
                $data['aggregate_id'] = (string) $row['aggregate_id'];
            }

            return $eventType::fromArray($data);
        } catch (\Throwable $exception) {
            throw new \RuntimeException(sprintf('Error deserializing event: %s', $exception->getMessage()), 0, $exception);
        }
    }
}
