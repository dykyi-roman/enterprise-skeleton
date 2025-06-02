<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Outbox;

use Doctrine\DBAL\Connection;
use RuntimeException;

final readonly class OutboxEventRepository
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * @throws RuntimeException
     */
    public function save(OutboxEvent $event): void
    {
        try {
            $this->connection->insert('outbox_events', [
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
            ]);
        } catch (\Throwable $exception) {
            throw new RuntimeException(
                sprintf('Ошибка при сохранении события в outbox %s', $exception->getMessage()),
                0,
                $exception,
            );
        }
    }

    /**
     * @throws RuntimeException
     */
    public function update(OutboxEvent $event): void
    {
        try {
            $this->connection->update(
                'outbox_events',
                [
                    'processed_at' => $event->processedAt?->format('Y-m-d H:i:s.u'),
                    'is_processed' => $event->isProcessed,
                    'retry_count' => $event->retryCount,
                    'error' => $event->error,
                ],
                ['id' => $event->getId()]
            );
        } catch (\Throwable $exception) {
            throw new RuntimeException(sprintf('Error updating event in: %s', $exception->getMessage()), 0, $exception);
        }
    }

    /**
     * @return array<OutboxEvent>
     * @throws RuntimeException
     */
    public function findUnprocessed(int $limit = 100): array
    {
        try {
            $stmt = $this->connection->createQueryBuilder()
                ->select('*')
                ->from('outbox_events')
                ->where('is_processed = :isProcessed')
                ->orderBy('created_at', 'ASC')
                ->setParameter('isProcessed', false)
                ->setMaxResults($limit)
                ->executeQuery();

            $events = [];
            while ($row = $stmt->fetchAssociative()) {
                $events[] = $this->hydrateOutboxEvent($row);
            }

            return $events;
        } catch (\Throwable $exception) {
            throw new RuntimeException(
                sprintf('Error receiving unhandled events:, %s', $exception->getMessage()),
                0,
                $exception,
            );
        }
    }

    /**
     * @param array<string, mixed> $data
     * @return OutboxEvent
     * @throws \DateMalformedStringException
     */
    private function hydrateOutboxEvent(array $data): OutboxEvent
    {
        return new OutboxEvent(
            $data['id'],
            $data['event_id'],
            $data['event_type'],
            $data['aggregate_id'],
            $data['payload'],
            new \DateTimeImmutable($data['created_at']),
            isset($data['processed_at']) ? new \DateTimeImmutable($data['processed_at']) : null,
            (bool)$data['is_processed'],
            (int)$data['retry_count'],
            $data['error']
        );
    }
}
