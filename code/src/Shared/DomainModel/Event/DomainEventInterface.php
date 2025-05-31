<?php

declare(strict_types=1);

namespace Shared\DomainModel\Event;

interface DomainEventInterface extends \JsonSerializable
{
    public function getEventId(): string;

    public function getOccurredAt(): \DateTimeImmutable;

    public function getEventName(): string;

    public function jsonSerialize(): array;
}
