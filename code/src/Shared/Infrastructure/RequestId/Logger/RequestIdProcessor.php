<?php

declare(strict_types=1);

namespace Shared\Infrastructure\RequestId\Logger;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Shared\DomainModel\Service\RequestIdServiceInterface;

final readonly class RequestIdProcessor implements ProcessorInterface
{
    public function __construct(
        private RequestIdServiceInterface $requestIdService,
    ) {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        if (!$this->requestIdService->hasRequestId()) {
            return $record;
        }

        $requestId = $this->requestIdService->getCurrentRequestId();

        return $record->with(
            extra: array_merge($record->extra, [
                'request_id' => $requestId->getValue(),
            ])
        );
    }
}
