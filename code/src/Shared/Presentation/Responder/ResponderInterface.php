<?php

declare(strict_types=1);

namespace Shared\Presentation\Responder;

interface ResponderInterface
{
    public function respond(): self;

    /** @return array<string, mixed> */
    public function payload(): array;

    public function statusCode(): int;

    /** @return array<string, string> */
    public function headers(): array;
}
