<?php

declare(strict_types=1);

namespace App\Healthcheck\Presentation\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:healthcheck:cassandra',
    description: 'Check Cassandra database connection',
)]
final class CassandraHealthcheckCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $host = getenv('CASSANDRA_HOST') ?: 'es-cassandra';
        $port = (int) (getenv('CASSANDRA_PORT') ?: 9042);

        try {
            $socket = @fsockopen($host, $port, $errno, $errstr, 5);
            if (false === $socket) {
                $output->writeln(sprintf('<error>Cassandra is not accessible: %s</error>', $errstr));

                return Command::FAILURE;
            }

            fclose($socket);
            $output->writeln('<info>Cassandra is healthy</info>');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Error checking Cassandra: %s</error>', $e->getMessage()));

            return Command::FAILURE;
        }
    }
}
