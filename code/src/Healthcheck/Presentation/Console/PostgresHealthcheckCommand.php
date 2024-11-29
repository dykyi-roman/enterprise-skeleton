<?php

declare(strict_types=1);

namespace App\Healthcheck\Presentation\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:healthcheck:postgres',
    description: 'Test PostgreSQL connection and basic operations',
)]
final class PostgresHealthcheckCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                getenv('POSTGRES_HOST') ?: 'es-postgres',
                getenv('POSTGRES_PORT') ?: '5432',
                getenv('POSTGRES_DB') ?: 'app'
            );

            $pdo = new \PDO(
                $dsn,
                getenv('POSTGRES_USER') ?: 'app',
                getenv('POSTGRES_PASSWORD') ?: 'password'
            );
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // Test query
            $stmt = $pdo->query('SELECT 1');
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result && 1 === $result['?column?']) {
                $output->writeln('<info>PostgreSQL connection test successful!</info>');

                return Command::SUCCESS;
            }

            $output->writeln('<error>PostgreSQL connection test failed: Unexpected result</error>');

            return Command::FAILURE;
        } catch (\PDOException $exception) {
            $output->writeln(sprintf('<error>PostgreSQL connection test failed: %s</error>', $exception->getMessage()));

            return Command::FAILURE;
        }
    }
}
