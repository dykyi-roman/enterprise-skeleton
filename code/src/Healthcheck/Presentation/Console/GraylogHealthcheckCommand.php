<?php

declare(strict_types=1);

namespace App\Healthcheck\Presentation\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:healthcheck:graylog',
    description: 'Check Graylog connection and functionality',
)]
final class GraylogHealthcheckCommand extends Command
{
    private const GRAYLOG_TEST_MESSAGE = 'Healthcheck test message';
    private const UDP_TIMEOUT = 5;

    public function __construct()
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $graylogHost = $_ENV['GRAYLOG_HOST'] ?? 'es-graylog';
            $graylogPort = $_ENV['GRAYLOG_PORT'] ?? '9000';
            $graylogUdpPort = $_ENV['GRAYLOG_UDP_PORT'] ?? '12201';

            if (!$this->checkGraylogConnection($graylogHost, $graylogPort)) {
                $io->error('Failed to connect to Graylog');

                return Command::FAILURE;
            }

            if (!$this->sendTestLogMessage($graylogHost, $graylogUdpPort)) {
                $io->error('Failed to send test message to Graylog');

                return Command::FAILURE;
            }

            $io->success('Graylog healthcheck passed successfully. Test message sent.');

            return Command::SUCCESS;
        } catch (\Throwable $exception) {
            $io->error('Healthcheck failed: '.$exception->getMessage());

            return Command::FAILURE;
        }
    }

    private function checkGraylogConnection(string $host, string $port): bool
    {
        $connection = @fsockopen($host, (int) $port, $errno, $errstr, self::UDP_TIMEOUT);

        if ($connection) {
            fclose($connection);

            return true;
        }

        return false;
    }

    private function sendTestLogMessage(string $host, string $port): bool
    {
        try {
            $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            if (false === $socket) {
                return false;
            }

            $message = json_encode([
                'version' => '1.1',
                'host' => gethostname(),
                'short_message' => self::GRAYLOG_TEST_MESSAGE,
                'timestamp' => time(),
                'level' => 6, // Info level
                '_application' => 'healthcheck',
                '_environment' => $_ENV['APP_ENV'] ?? 'dev',
            ]);

            socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => self::UDP_TIMEOUT, 'usec' => 0]);
            socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => self::UDP_TIMEOUT, 'usec' => 0]);

            $result = socket_sendto($socket, $message, strlen($message), 0, $host, (int) $port);
            socket_close($socket);

            return false !== $result;
        } catch (\Throwable) {
            return false;
        }
    }
}
