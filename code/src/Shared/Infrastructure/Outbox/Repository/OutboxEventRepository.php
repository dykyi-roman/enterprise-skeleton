<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Outbox\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Shared\Infrastructure\Outbox\ValueObject\OutboxEvent;

final readonly class OutboxEventRepository
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * @throws \RuntimeException
     */
    public function save(OutboxEvent $event): void
    {
        try {
            $this->connection->insert(
                'outbox_events',
                [
                    'id' => $event->getId(),
                    'event_id' => $event->getEventId(),
                    'event_type' => $event->getEventType(),
                    'aggregate_id' => $event->getAggregateId(),
                    'payload' => $event->getPayload(),
                    'created_at' => $event->getCreatedAt()->format('Y-m-d H:i:s.u'),
                    'processed_at' => $event->processedAt?->format('Y-m-d H:i:s.u'),
                    'is_processed' => $event->isProcessed,
                    'retry_count' => $event->retryCount,
                    'error' => $event->error,
                ],
                [
                    'id' => Types::STRING,
                    'event_id' => Types::STRING,
                    'event_type' => Types::STRING,
                    'aggregate_id' => Types::STRING,
                    'payload' => Types::TEXT,
                    'created_at' => Types::STRING,
                    'processed_at' => Types::STRING,
                    'is_processed' => Types::BOOLEAN,
                    'retry_count' => Types::INTEGER,
                    'error' => Types::TEXT,
                ]
            );
        } catch (\Throwable $exception) {
            throw new \RuntimeException(sprintf('Error saving event to outbox %s', $exception->getMessage()), 0, $exception);
        }
    }

    /**
     * @throws \RuntimeException
     */
    public function update(OutboxEvent $event): void
    {
        try {
            $this->connection->update(
                'outbox_events',
                [
                    'processed_at' => $event->processedAt?->format('Y-m-d H:i:s.u'),
                    'is_processed' => $event->isProcessed ? true : false,
                    'retry_count' => (int) $event->retryCount,
                    'error' => $event->error,
                ],
                ['id' => $event->getId()],
                [
                    'processed_at' => Types::STRING,
                    'is_processed' => Types::BOOLEAN,
                    'retry_count' => Types::INTEGER,
                    'error' => Types::TEXT,
                    'id' => Types::STRING,
                ]
            );
        } catch (\Throwable $exception) {
            throw new \RuntimeException(sprintf('Error updating event in: %s', $exception->getMessage()), 0, $exception);
        }
    }

    /**
     * @return array<OutboxEvent>
     *
     * @throws \RuntimeException
     */
    public function findUnprocessed(int $limit = 100): array
    {
        try {
            $stmt = $this->connection->executeQuery(
                'SELECT * FROM outbox_events WHERE is_processed = :isProcessed ORDER BY created_at ASC LIMIT :limit',
                [
                    'isProcessed' => false,
                    'limit' => $limit,
                ],
                [
                    'isProcessed' => Types::BOOLEAN,
                    'limit' => Types::INTEGER,
                ]
            );

            $events = [];
            while ($row = $stmt->fetchAssociative()) {
                $events[] = $this->hydrateOutboxEvent($row);
            }

            return $events;
        } catch (\Throwable $exception) {
            throw new \RuntimeException(sprintf('Error receiving unhandled events:, %s', $exception->getMessage()), 0, $exception);
        }
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws \DateMalformedStringException
     */
    private function hydrateOutboxEvent(array $data): OutboxEvent
    {
        $processedAt = isset($data['processed_at']) && is_string($data['processed_at'])
            ? new \DateTimeImmutable($data['processed_at'])
            : null;

        return new OutboxEvent(
            is_scalar($data['id']) ? (string) $data['id'] : '',
            is_scalar($data['event_id']) ? (string) $data['event_id'] : '',
            is_scalar($data['event_type']) ? (string) $data['event_type'] : '',
            is_scalar($data['aggregate_id']) ? (string) $data['aggregate_id'] : '',
            is_scalar($data['payload']) ? (string) $data['payload'] : '',
            new \DateTimeImmutable(is_scalar($data['created_at']) ? (string) $data['created_at'] : 'now'),
            $processedAt,
            (bool) ($data['is_processed'] ?? false),
            isset($data['retry_count']) && is_numeric($data['retry_count']) ? (int) $data['retry_count'] : 0,
            isset($data['error']) && is_scalar($data['error']) ? (string) $data['error'] : null
        );
    }
}
