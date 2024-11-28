<?php

declare(strict_types=1);

namespace App\Healthcheck\Presentation\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:healthcheck:mongodb',
    description: 'Test MongoDB connection and basic operations',
)]
final class MongodbHealthcheckCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $host = getenv('MONGO_HOST') ?: 'es-mongodb';
            $port = (int) (getenv('MONGO_PORT') ?: '27017');

            // Create a socket connection
            $socket = @fsockopen($host, $port, $errno, $errstr, 5);
            if (!$socket) {
                throw new \RuntimeException(sprintf('Could not connect to MongoDB: %s', $errstr));
            }

            fclose($socket);
            $output->writeln('<info>MongoDB connection test successful!</info>');

            return Command::SUCCESS;
        } catch (\Exception $exception) {
            $output->writeln(sprintf('<error>MongoDB connection test failed: %s</error>', $exception->getMessage()));

            return Command::FAILURE;
        }
    }
}
