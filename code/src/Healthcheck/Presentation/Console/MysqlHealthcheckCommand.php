<?php

declare(strict_types=1);

namespace App\Healthcheck\Presentation\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:healthcheck:mysql',
    description: 'Test MySQL connection and basic operations',
)]
final class MysqlHealthcheckCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s',
                getenv('MYSQL_HOST') ?: 'es-mysql',
                getenv('MYSQL_PORT') ?: '3306',
                getenv('MYSQL_DB') ?: 'app'
            );

            $pdo = new \PDO(
                $dsn,
                getenv('MYSQL_USER') ?: 'app',
                getenv('MYSQL_PASSWORD') ?: 'password'
            );
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // Test query
            $stmt = $pdo->query('SELECT 1');
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result && 1 === $result['1']) {
                $output->writeln('<info>MySQL connection test successful!</info>');

                return Command::SUCCESS;
            }

            $output->writeln('<error>MySQL connection test failed: Unexpected result</error>');

            return Command::FAILURE;
        } catch (\PDOException $exception) {
            $output->writeln(sprintf('<error>MySQL connection test failed: %s</error>', $exception->getMessage()));

            return Command::FAILURE;
        }
    }
}
