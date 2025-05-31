<?php

declare(strict_types=1);

namespace Healthcheck\Presentation\Console;

use Psr\Log\LoggerInterface;
use Shared\Presentation\Console\Command\AbstractConsoleCommand;
use Shared\Presentation\Console\Output\ConsoleOutput;
use Shared\Presentation\Responder\ConsoleResponder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:healthcheck:logs',
    description: 'Test different types of logging'
)]
final class LogHealthcheckCommand extends AbstractConsoleCommand
{
    public function __construct(
        private readonly LoggerInterface $logger,
        ConsoleResponder $responder,
    ) {
        parent::__construct($responder);
    }

    /**
     * @throws \Exception
     */
    protected function executeCommand(InputInterface $input, OutputInterface $output): ConsoleOutput
    {
        $messages = [];
        $messages[] = ConsoleOutput::formatMessage('Starting logging check', 'note');
        $messages[] = ConsoleOutput::formatMessage('Logging test in progress...', 'info');

        $this->logger->info('This is info log message');
        $this->logger->error('This is error message');

        try {
            throw new \Exception('Test exception');
        } catch (\Throwable $exception) {
            $messages[] = ConsoleOutput::formatMessage('An error has been detected: '.$exception->getMessage(), 'error');

            $this->logger->critical('Critical error occurred', [
                'exception' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }

        $messages[] = ConsoleOutput::formatMessage('Logging tests completed', 'success');

        return ConsoleOutput::success(
            $messages,
            'Healthcheck: Testing logging',
            [],
            'All logging tests completed successfully.'
        );
    }
}
