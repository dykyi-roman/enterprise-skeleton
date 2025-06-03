<?php

declare(strict_types=1);

namespace Shared\DomainModel\Event;

interface DomainEventInterface extends \JsonSerializable
{
    public function getEventId(): string;

    public function getAggregateId(): string;

    public function getOccurredAt(): \DateTimeImmutable;

    public function getEventName(): string;

    public function getVersion(): int;

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array;

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static;
}
