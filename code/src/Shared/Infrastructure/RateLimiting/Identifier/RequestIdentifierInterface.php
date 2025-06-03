<?php

declare(strict_types=1);

namespace Shared\Infrastructure\RateLimiting\Identifier;

use Symfony\Component\HttpFoundation\Request;

interface RequestIdentifierInterface
{
    /**
     * Extract client IP as request identifier.
     */
    public function getIdentifier(Request $request): string;
}
