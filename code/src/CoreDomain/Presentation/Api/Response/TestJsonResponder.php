<?php

declare(strict_types=1);

namespace App\CoreDomain\Presentation\Api\Response;

use App\Shared\Presentation\Responder\ResponderInterface;

final class TestJsonResponder implements ResponderInterface, \Stringable
{
    /** @var array<string, mixed> */
    private array $data = [];
    private int $statusCode = 200;

    /** @return array<string, mixed> */
    public function payload(): array
    {
        return $this->data;
    }

    public function success(string $message): self
    {
        $this->data = [
            'success' => true,
            'message' => $message,
        ];
        $this->statusCode = 200;

        return $this;
    }

    public function error(string $message): self
    {
        $this->data = [
            'success' => false,
            'errors' => [
                'message' => $message,
            ],
        ];
        $this->statusCode = 500;

        return $this;
    }

    public function respond(): self
    {
        return $this;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function jsonSerialize(): string
    {
        return serialize($this);
    }

    public function __toString()
    {
        return $this::class;
    }
}
