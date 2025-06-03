<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Outbox\Command;

/**
 * Envelope for message that wraps the payload and metadata
 * This class provides a clean abstraction between domain events and infrastructure.
 *
 * @see OutboxMessageEnvelopeHandler
 */
final readonly class OutboxMessageEnvelope implements \JsonSerializable
{
    /**
     * @param string               $payload  JSON serialized message content
     * @param array<string, mixed> $metadata Message metadata
     */
    public function __construct(
        private string $payload,
        private array $metadata = [],
    ) {
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getMetadataValue(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    public function hasMetadataKey(string $key): bool
    {
        return array_key_exists($key, $this->metadata);
    }

    /**
     * @return array{payload: string, metadata: array<string, mixed>}
     */
    public function jsonSerialize(): array
    {
        return [
            'payload' => $this->payload,
            'metadata' => $this->metadata,
        ];
    }
}
