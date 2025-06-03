<?php

declare(strict_types=1);

namespace Shared\Infrastructure\RateLimiting\Identifier;

use Symfony\Component\HttpFoundation\Request;

/**
 * IP-based request identifier implementation.
 */
final readonly class IpIdentifier implements RequestIdentifierInterface
{
    /**
     * Extract client IP as request identifier.
     */
    public function getIdentifier(Request $request): string
    {
        $clientIp = $request->getClientIp();

        if (null === $clientIp) {
            // Fallback to a random identifier if client IP is not available
            return 'unknown_'.md5(random_bytes(16));
        }

        return $clientIp;
    }
}
