<?php

declare(strict_types=1);

namespace Shared\DomainModel\Service;

use Shared\DomainModel\ValueObject\RequestId;

interface RequestIdServiceInterface
{
    /**
     * Get current request ID.
     */
    public function getCurrentRequestId(): RequestId;

    /**
     * Set current request ID.
     */
    public function setCurrentRequestId(RequestId $requestId): void;

    /**
     * Generate and set new request ID.
     */
    public function generateAndSetRequestId(): RequestId;

    /**
     * Check if request ID is set.
     */
    public function hasRequestId(): bool;

    /**
     * Clear current request ID.
     */
    public function clearRequestId(): void;
}
