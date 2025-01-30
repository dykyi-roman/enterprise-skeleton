<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Responder;

/**
 * @see \Stringable interface use like a trick. Because framwork do not want parse an object class in the request.
 */
interface ResponderInterface extends \Stringable
{
    public function respond(): self;

    /** @return array<string, mixed> */
    public function payload(): array;

    public function statusCode(): int;
}
