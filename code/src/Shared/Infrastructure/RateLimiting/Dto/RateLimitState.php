<?php

declare(strict_types=1);

namespace Shared\Infrastructure\RateLimiting\Dto;

final readonly class RateLimitState
{
    public function __construct(
        public int $count,
        public int $timestamp,
        public int $windowSize,
    ) {
    }

    public function withIncrementedCount(): self
    {
        return new self(
            count: $this->count + 1,
            timestamp: $this->timestamp,
            windowSize: $this->windowSize,
        );
    }

    public function withWindowSize(int $windowSize): self
    {
        return new self(
            count: $this->count,
            timestamp: $this->timestamp,
            windowSize: $windowSize,
        );
    }

    public static function createInitial(int $windowSize): self
    {
        return new self(
            count: 1,
            timestamp: time(),
            windowSize: $windowSize,
        );
    }

    public function isWindowExpired(): bool
    {
        return (time() - $this->timestamp) >= $this->windowSize;
    }

    public function getRemainingTime(): int
    {
        $elapsed = time() - $this->timestamp;

        if ($elapsed >= $this->windowSize) {
            return 0;
        }

        return $this->windowSize - $elapsed;
    }
}
