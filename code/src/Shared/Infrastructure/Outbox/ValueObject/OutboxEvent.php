<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Outbox\ValueObject;

final class OutboxEvent
{
    /**
     * @param string                  $id          Unique identifier of the record
     * @param string                  $eventId     Event identifier
     * @param string                  $eventType   Event type
     * @param string                  $aggregateId Aggregate identifier
     * @param string                  $payload     Serialized event data
     * @param \DateTimeImmutable      $createdAt   Date and time of record creation
     * @param \DateTimeImmutable|null $processedAt Date and time of record processing
     * @param bool                    $isProcessed Processing status
     * @param int                     $retryCount  Number of processing attempts
     * @param string|null             $error       Description of the error during the last processing attempt
     */
    public function __construct(
        private readonly string $id,
        private readonly string $eventId,
        private readonly string $eventType,
        private readonly string $aggregateId,
        private readonly string $payload,
        private readonly \DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $processedAt = null {
            get
    {
        return $this->processedAt;
    }
        },
        public bool $isProcessed = false {
            get {
                return $this->isProcessed;
            }
        },
        public int $retryCount = 0 {
            get {
                return $this->retryCount;
            }
        },
        public ?string $error = null {
            get {
                return $this->error;
            }
        },
    ) {
    }

    public static function create(string $eventId, string $eventType, string $aggregateId, string $payload): self
    {
        return new self(
            uuid_create(),
            $eventId,
            $eventType,
            $aggregateId,
            $payload,
            new \DateTimeImmutable()
        );
    }

    public function markAsProcessed(): void
    {
        $this->isProcessed = true;
        $this->processedAt = new \DateTimeImmutable();
    }

    public function increaseRetryCount(string $error): void
    {
        ++$this->retryCount;
        $this->error = $error;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEventId(): string
    {
        return $this->eventId;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function getAggregateId(): string
    {
        return $this->aggregateId;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
