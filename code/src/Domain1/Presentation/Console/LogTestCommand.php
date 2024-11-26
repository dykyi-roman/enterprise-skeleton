<?php

declare(strict_types=1);

namespace App\Domain1\Presentation\Console;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:log:test',
    description: 'Test different types of logging'
)]
final class LogTestCommand extends Command
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('This is console output');
        $this->logger->info('This is info log message');
        $this->logger->error('This is error message');
        
        try {
            throw new \Exception('Test exception');
        } catch (\Throwable $e) {
            $this->logger->critical('Critical error occurred', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return Command::SUCCESS;
    }
}
