<?php

declare(strict_types=1);

namespace Shared\Infrastructure\RequestId;

use Shared\DomainModel\Service\RequestIdServiceInterface;
use Shared\DomainModel\ValueObject\RequestId;

final class RequestIdService implements RequestIdServiceInterface
{
    private ?RequestId $currentRequestId = null;

    public function getCurrentRequestId(): RequestId
    {
        if (null === $this->currentRequestId) {
            throw new \RuntimeException('Request ID is not set');
        }

        return $this->currentRequestId;
    }

    public function setCurrentRequestId(RequestId $requestId): void
    {
        $this->currentRequestId = $requestId;
    }

    public function generateAndSetRequestId(): RequestId
    {
        $requestId = RequestId::generate();
        $this->setCurrentRequestId($requestId);

        return $requestId;
    }

    public function hasRequestId(): bool
    {
        return null !== $this->currentRequestId;
    }

    public function clearRequestId(): void
    {
        $this->currentRequestId = null;
    }
}
