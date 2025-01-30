<?php

declare(strict_types=1);

namespace App\Healthcheck\Presentation\Web\Response;

use App\Shared\Presentation\Responder\TemplateResponderInterface;

final class TestActionHtmlResponse implements TemplateResponderInterface
{
    /** @var array<string, mixed> */
    private array $data = [];

    public function template(): string
    {
        return 'healthcheck::test';
    }

    /** @return array<string, mixed> */
    public function payload(): array
    {
        return $this->data;
    }

    /** @param array<string, mixed> $data */
    public function context(array $data = []): self
    {
        $this->data = $data;

        return $this;
    }

    public function respond(): self
    {
        return $this;
    }

    public function statusCode(): int
    {
        return 200;
    }

    public function headers(): array
    {
        return ['Content-Type' => 'text/html'];
    }

    public function __toString(): string
    {
        return $this::class;
    }
}
