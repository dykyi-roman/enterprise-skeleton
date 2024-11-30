<?php

declare(strict_types=1);

namespace App\Healthcheck\Presentation\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:healthcheck:logstash',
    description: 'Send a test message to Logstash to verify connectivity'
)]
final class LogstashHealthcheckCommand extends Command
{
    private const string LOGSTASH_HOST = 'es-logstash';
    private const int LOGSTASH_PORT = 50000;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $socket = @fsockopen(self::LOGSTASH_HOST, self::LOGSTASH_PORT, $errno, $errstr, 5);

            if (!$socket) {
                throw new \RuntimeException("Could not connect to Logstash: $errstr ($errno)");
            }

            $testMessage = json_encode([
                '@timestamp' => date('c'),
                'message' => 'Logstash healthcheck test message',
                'environment' => 'test',
                'service' => 'healthcheck',
                'type' => 'test',
            ])."\n";

            fwrite($socket, $testMessage);
            fclose($socket);

            $output->writeln('<info>Test message sent to Logstash successfully!</info>');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Failed to send test message to Logstash: '.$e->getMessage().'</error>');

            return Command::FAILURE;
        }
    }
}
