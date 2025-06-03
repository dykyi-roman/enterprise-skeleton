<?php

declare(strict_types=1);

namespace Shared\DomainModel\ValueObject;

abstract readonly class AbstractValueObject implements \JsonSerializable, \Stringable
{
    public function equals(self $other): bool
    {
        return $this->jsonSerialize() === $other->jsonSerialize();
    }

    abstract public function __toString(): string;

    abstract public function jsonSerialize(): mixed;

    /**
     * @param array<string, mixed> $data
     */
    abstract public static function fromArray(array $data): static;
}
