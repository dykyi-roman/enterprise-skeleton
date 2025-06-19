<?php

declare(strict_types=1);

namespace Shared\DomainModel\ValueObject;

final readonly class RequestId
{
    private function __construct(
        private string $value,
    ) {
        $this->validate($value);
    }

    public static function generate(): self
    {
        return new self(self::generateUuid());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(RequestId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private function validate(string $value): void
    {
        if (empty($value)) {
            throw new \InvalidArgumentException('RequestId cannot be empty');
        }

        if (strlen($value) > 255) {
            throw new \InvalidArgumentException('RequestId cannot be longer than 255 characters');
        }

        // Allow UUID format or any alphanumeric string with hyphens and underscores
        if (!preg_match('/^[a-zA-Z0-9\-_]+$/', $value)) {
            throw new \InvalidArgumentException('RequestId contains invalid characters');
        }
    }

    private static function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0x0FFF) | 0x4000,
            mt_rand(0, 0x3FFF) | 0x8000,
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF)
        );
    }
}
