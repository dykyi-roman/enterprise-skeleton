<?php

declare(strict_types=1);

namespace Healthcheck\Presentation\Web\Resonse;

use Shared\Presentation\Responder\TemplateResponderInterface;

final class TestHtmlResponse implements TemplateResponderInterface
{
    /** @var array<string, mixed> */
    private array $data = [];

    #[\Override]
    public function template(): string
    {
        return '<h1>Lang: {{lang}}</h1>';
    }

    /** @return array<string, mixed> */
    #[\Override]
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

    #[\Override]
    public function respond(): self
    {
        return $this;
    }

    #[\Override]
    public function statusCode(): int
    {
        return 200;
    }

    #[\Override]
    public function headers(): array
    {
        return ['Content-Type' => 'text/html'];
    }
}
