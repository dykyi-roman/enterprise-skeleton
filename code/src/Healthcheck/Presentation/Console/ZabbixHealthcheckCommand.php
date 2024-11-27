<?php

declare(strict_types=1);

namespace App\Healthcheck\Presentation\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:healthcheck:zabbix',
    description: 'Send healthcheck data to Zabbix'
)]
class ZabbixHealthcheckCommand extends Command
{
    private const SOCKET_TIMEOUT = 5; // 5 seconds timeout
    private const ZBX_HEADER = "ZBXD\1";
    private const ZBX_HEADER_LENGTH = 13; // 5 bytes header + 8 bytes data length

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $zabbixServer = getenv('ZABBIX_SERVER_HOST') ?: 'zabbix-server';
        $zabbixPort = (int) (getenv('ZABBIX_SERVER_PORT') ?: 10051);
        $hostName = gethostname();

        $output->writeln(sprintf('<info>Connecting to Zabbix server at %s:%d</info>', $zabbixServer, $zabbixPort));

        // Create Zabbix sender packet
        $data = [
            'request' => 'sender data',
            'data' => [
                [
                    'host' => $hostName,
                    'key' => 'php.status',
                    'value' => '1',
                    'clock' => time(),
                ],
            ],
        ];

        $jsonData = json_encode($data);
        $jsonLength = strlen($jsonData);

        // Create binary header
        $packet = self::ZBX_HEADER;
        $packet .= pack('P', $jsonLength);
        $packet .= $jsonData;

        $output->writeln(sprintf('<info>Prepared packet: header length=%d, json length=%d</info>',
            self::ZBX_HEADER_LENGTH,
            $jsonLength
        ));

        // Create socket with error handling
        $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (false === $socket) {
            $error = socket_last_error();
            $output->writeln(sprintf('<error>Failed to create socket: %s</error>', socket_strerror($error)));

            return Command::FAILURE;
        }

        // Set socket timeout
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => self::SOCKET_TIMEOUT, 'usec' => 0]);
        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => self::SOCKET_TIMEOUT, 'usec' => 0]);

        // Try to connect with timeout
        $output->writeln('<info>Attempting to connect...</info>');
        if (false === @socket_connect($socket, $zabbixServer, $zabbixPort)) {
            $error = socket_last_error($socket);
            $output->writeln(sprintf('<error>Failed to connect to Zabbix server: %s</error>', socket_strerror($error)));
            socket_close($socket);

            return Command::FAILURE;
        }

        $output->writeln('<info>Connected successfully, sending data...</info>');

        // Send data
        $written = @socket_write($socket, $packet, strlen($packet));
        if (false === $written) {
            $error = socket_last_error($socket);
            $output->writeln(sprintf('<error>Failed to send data: %s</error>', socket_strerror($error)));
            socket_close($socket);

            return Command::FAILURE;
        }

        $output->writeln(sprintf('<info>Sent %d bytes, waiting for response...</info>', $written));

        // Read header first
        $header = '';
        $headerLength = self::ZBX_HEADER_LENGTH;
        while (strlen($header) < $headerLength) {
            $buffer = '';
            $received = @socket_recv($socket, $buffer, $headerLength - strlen($header), MSG_WAITALL);
            if (false === $received || 0 === $received) {
                break;
            }
            $header .= $buffer;
        }

        if (strlen($header) < $headerLength) {
            $output->writeln('<error>Failed to receive complete header</error>');
            socket_close($socket);

            return Command::FAILURE;
        }

        // Parse response length from header
        $responseHeader = substr($header, 0, 5);
        if (self::ZBX_HEADER !== $responseHeader) {
            $output->writeln('<error>Invalid response header</error>');
            socket_close($socket);

            return Command::FAILURE;
        }

        $responseLength = unpack('P', substr($header, 5, 8))[1];
        $output->writeln(sprintf('<info>Response header received, expecting %d bytes of data</info>', $responseLength));

        // Read response data
        $response = '';
        while (strlen($response) < $responseLength) {
            $buffer = '';
            $toRead = min(1024, $responseLength - strlen($response));
            $received = @socket_recv($socket, $buffer, $toRead, MSG_WAITALL);
            if (false === $received || 0 === $received) {
                break;
            }
            $response .= $buffer;
        }

        socket_close($socket);

        if (strlen($response) < $responseLength) {
            $output->writeln('<error>Failed to receive complete response data</error>');

            return Command::FAILURE;
        }

        $responseData = json_decode($response, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            $output->writeln('<error>Failed to parse JSON response</error>');

            return Command::FAILURE;
        }

        $output->writeln('<info>Response received:</info>');
        $output->writeln(json_encode($responseData, JSON_PRETTY_PRINT));

        return isset($responseData['response']) && 'success' === $responseData['response']
            ? Command::SUCCESS
            : Command::FAILURE;
    }
}
