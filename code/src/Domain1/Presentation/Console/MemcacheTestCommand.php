<?php

declare(strict_types=1);

namespace App\Domain1\Presentation\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:test:memcached',
    description: 'Test Memcached connection'
)]
final class MemcacheTestCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $memcached = new \Memcached();
            $connected = $memcached->addServer('es-memcached', 11211);
            if (!$connected) {
                throw new \RuntimeException('Could not connect to Memcached server');
            }

            // Test SET operation
            $memcached->set('test_key', 'Hello from Memcached!', 60);
            $output->writeln('<info>Successfully set test key</info>');

            // Test GET operation
            $value = $memcached->get('test_key');
            $output->writeln(sprintf('<info>Retrieved value: %s</info>', $value));

            // Test DELETE operation
            $memcached->delete('test_key');
            $output->writeln('<info>Successfully deleted test key</info>');

            // Test server stats
            $stats = $memcached->getStats();
            if ($stats) {
                foreach ($stats as $server => $serverStats) {
                    $output->writeln('<info>Server statistics:</info>');
                    $output->writeln(sprintf('<info>- Uptime: %d seconds</info>', $serverStats['uptime']));
                    $output->writeln(sprintf('<info>- Current items: %d</info>', $serverStats['curr_items']));
                    $output->writeln(sprintf('<info>- Total connections: %d</info>', $serverStats['total_connections']));
                }
            }

            $output->writeln('<info>Memcached connection test completed successfully!</info>');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Memcached test failed: %s</error>', $e->getMessage()));

            return Command::FAILURE;
        }
    }
}
