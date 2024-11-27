<?php

declare(strict_types=1);

namespace App\Healthcheck\Presentation\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:healthcheck:redis',
    description: 'Test Redis connection'
)]
final class RedisHealthcheckCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $redis = new \Redis();
            $redis->connect('es-redis', 6379, 2.0);

            // Test SET operation
            $redis->set('test_key', 'Hello from Redis!');
            $output->writeln('<info>Successfully set test key</info>');

            // Test GET operation
            $value = $redis->get('test_key');
            $output->writeln(sprintf('<info>Retrieved value: %s</info>', $value));

            // Test DELETE operation
            $redis->del('test_key');
            $output->writeln('<info>Successfully deleted test key</info>');

            $output->writeln('<info>Redis connection test completed successfully!</info>');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Redis test failed: %s</error>', $e->getMessage()));

            return Command::FAILURE;
        }
    }
}
