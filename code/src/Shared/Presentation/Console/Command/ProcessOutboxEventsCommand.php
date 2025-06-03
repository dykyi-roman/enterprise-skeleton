<?php

declare(strict_types=1);

namespace Shared\Presentation\Console\Command;

use Psr\Log\LoggerInterface;
use Shared\Infrastructure\Outbox\Service\OutboxEventProcessor;
use Shared\Presentation\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:process-outbox',
    description: 'Process outbox events and publish them to message broker'
)]
final class ProcessOutboxEventsCommand extends AbstractConsoleCommand
{
    public function __construct(
        private readonly OutboxEventProcessor $outboxProcessor,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'batch-size',
                'b',
                InputOption::VALUE_OPTIONAL,
                'Number of events to process in one batch',
                100
            )
            ->addOption(
                'iterations',
                'i',
                InputOption::VALUE_OPTIONAL,
                'Number of iterations to run (0 for infinite)',
                1
            )
            ->addOption(
                'delay',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Delay in seconds between iterations',
                5
            )
            ->addOption(
                'daemon',
                'D',
                InputOption::VALUE_NONE,
                'Run in daemon mode (continuously processing events)'
            );
    }

    protected function executeCommand(InputInterface $input, OutputInterface $output): ConsoleOutput
    {
        $batchSize = (int) $input->getOption('batch-size');
        $iterations = (int) $input->getOption('iterations');
        $delay = (int) $input->getOption('delay');
        $daemonMode = (bool) $input->getOption('daemon');

        // In daemon mode, we run indefinitely
        if ($daemonMode) {
            $iterations = 0;
            $this->logger->info('Starting outbox event processing in daemon mode', [
                'batch_size' => $batchSize,
                'delay' => $delay,
            ]);
        } else {
            $this->logger->info('Starting outbox event processing', [
                'batch_size' => $batchSize,
                'iterations' => $iterations,
                'delay' => $delay,
            ]);
        }

        $totalProcessed = 0;
        $iteration = 0;
        $messages = [];

        $messages[] = ConsoleOutput::formatMessage(
            $daemonMode ? 'Starting outbox processing in daemon mode...' : 'Starting outbox processing...',
            'info'
        );

        try {
            do {
                ++$iteration;
                $messages[] = ConsoleOutput::formatMessage(
                    sprintf('Processing batch %d...', $iteration),
                    'comment'
                );

                $processed = $this->outboxProcessor->processOutboxEvents($batchSize);
                $totalProcessed += $processed;

                $messages[] = ConsoleOutput::formatMessage(
                    sprintf('Processed %d events in batch %d', $processed, $iteration),
                    'info'
                );

                $this->logger->info('Processed events batch', [
                    'batch' => $iteration,
                    'processed' => $processed,
                    'total_processed' => $totalProcessed,
                    'daemon_mode' => $daemonMode,
                ]);

                if (0 === $processed && (0 === $iterations || $iteration < $iterations)) {
                    $messages[] = ConsoleOutput::formatMessage(
                        sprintf('No events to process, waiting for %d seconds...', $delay),
                        'comment'
                    );
                    sleep($delay);
                }
            } while (0 === $iterations || $iteration < $iterations);

            $this->logger->info('Outbox event processing completed', [
                'total_processed' => $totalProcessed,
                'daemon_mode' => $daemonMode,
            ]);

            return ConsoleOutput::success(
                $messages,
                'Outbox Event Processing',
                [
                    'processed_events' => $totalProcessed,
                    'iterations' => $iteration,
                    'daemon_mode' => $daemonMode,
                ],
                sprintf('Outbox processing completed. Total processed: %d events', $totalProcessed),
            );
        } catch (\Exception $e) {
            $this->logger->error('Error processing outbox events', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'daemon_mode' => $daemonMode,
            ]);

            $messages[] = ConsoleOutput::formatMessage(
                sprintf('Error processing outbox events: %s', $e->getMessage()),
                'error'
            );

            return ConsoleOutput::failure(
                $messages,
                'Outbox Event Processing',
                sprintf('Error: %s', $e->getMessage())
            );
        }
    }
}
