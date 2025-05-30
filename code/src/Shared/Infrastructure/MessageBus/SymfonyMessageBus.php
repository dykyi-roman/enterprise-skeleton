<?php

declare(strict_types=1);

namespace Shared\Infrastructure\MessageBus;

use Shared\DomainModel\Service\MessageBusInterface;
use Symfony\Component\Messenger\MessageBusInterface as SymfonyMessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final readonly class SymfonyMessageBus implements MessageBusInterface
{
    public function __construct(
        private SymfonyMessageBusInterface $messageBus,
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function dispatch(object $message): mixed
    {
        try {
            $envelope = $this->messageBus->dispatch($message);

            /** @var HandledStamp|null $stamp */
            $stamp = $envelope->last(HandledStamp::class);
        } catch (\Throwable $busException) {
            throw $busException->getPrevious() ?? $busException;
        }

        return $stamp?->getResult();
    }
}
