<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Outbox\Service;

use Psr\Log\LoggerInterface;
use Shared\DomainModel\Service\MessageBusInterface;
use Shared\Infrastructure\Outbox\Command\OutboxMessageEnvelope;
use Shared\Infrastructure\Outbox\Repository\OutboxEventRepository;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

final readonly class OutboxEventProcessor
{
    public function __construct(
        private OutboxEventRepository $outboxRepository,
        private MessageBusInterface $messageBus,
        private LockFactory $lockFactory,
        private LoggerInterface $logger,
        private string $exchangeName = 'order_events',
    ) {
    }

    /**
     * Processes unprocessed events from Outbox.
     *
     * The process uses locking to ensure that only one instance of the processor is
     * running at any given time, ensuring that messages are not duplicated.
     *
     * @param int $batchSize Number of events processed per call
     *
     * @return int Number of events successfully processed
     *
     * @throws \Exception If an unexpected error occurs
     */
    public function processOutboxEvents(int $batchSize = 100): int
    {
        $lock = $this->createLock();

        if (!$lock->acquire()) {
            $this->logger->info('Outbox processor is already running');

            return 0;
        }

        try {
            $events = $this->outboxRepository->findUnprocessed($batchSize);
            if (empty($events)) {
                $this->logger->debug('No unprocessed outbox events found');

                return 0;
            }

            $this->logger->info(sprintf('Found %d unprocessed outbox events', count($events)));

            $successCount = 0;
            foreach ($events as $event) {
                try {
                    $messageEnvelope = new OutboxMessageEnvelope(
                        $event->getPayload(),
                        [
                            'message_id' => $event->getId(),
                            'timestamp' => time(),
                            'type' => $event->getEventType(),
                            'routing_key' => $this->getRoutingKeyFromEventType($event->getEventType()),
                            'exchange' => $this->exchangeName,
                        ]
                    );

                    $this->messageBus->dispatch($messageEnvelope);

                    $event->markAsProcessed();
                    $this->outboxRepository->update($event);

                    ++$successCount;

                    $this->logger->info(sprintf(
                        'Successfully published outbox event %s of type %s',
                        $event->getId(),
                        $event->getEventType()
                    ));
                } catch (\Throwable $e) {
                    $this->logger->error(sprintf(
                        'Error publishing outbox event %s: %s',
                        $event->getId(),
                        $e->getMessage()
                    ));

                    $event->increaseRetryCount($e->getMessage());
                    $this->outboxRepository->update($event);
                }
            }

            return $successCount;
        } finally {
            $lock->release();
        }
    }

    private function createLock(): LockInterface
    {
        return $this->lockFactory->createLock('outbox_event_processor', 60);
    }

    /**
     * @param string $eventType Full event class name
     *
     * @return string Routing key for message broker
     */
    private function getRoutingKeyFromEventType(string $eventType): string
    {
        // Extract the short name of the event class
        $parts = explode('\\', $eventType);
        $shortName = end($parts);

        // Convert to snake_case for use as routing key
        $routingKey = preg_replace('/(?<!^)[A-Z]/', '_$0', $shortName);
        $routingKey = strtolower(str_replace('_event', '', $routingKey));

        return 'order.'.$routingKey;
    }
}
