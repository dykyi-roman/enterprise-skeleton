<?php

declare(strict_types=1);

namespace App\Healthcheck\Presentation\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:healthcheck:amqp',
    description: 'Test AMQP connection and send a test message'
)]
final class AmqpHealthcheckCommand extends Command
{
    private const string QUEUE_NAME = 'test_queue';
    private const string EXCHANGE_NAME = 'test_exchange';
    private const string ROUTING_KEY = 'test_key';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            // Create connection
            $connection = new \AMQPConnection([
                'host' => 'es-rabbitmq',
                'port' => 5672,
                'vhost' => '/',
                'login' => 'app',
                'password' => 'password',
            ]);
            $connection->connect();
            $output->writeln('<info>Successfully connected to RabbitMQ</info>');

            // Create channel
            $channel = new \AMQPChannel($connection);

            // Declare exchange
            $exchange = new \AMQPExchange($channel);
            $exchange->setName(self::EXCHANGE_NAME);
            $exchange->setType(AMQP_EX_TYPE_DIRECT);
            $exchange->setFlags(AMQP_DURABLE);
            $exchange->declare();
            $output->writeln('<info>Exchange declared</info>');

            // Declare queue
            $queue = new \AMQPQueue($channel);
            $queue->setName(self::QUEUE_NAME);
            $queue->setFlags(AMQP_DURABLE);
            $queue->declare();
            $queue->bind(self::EXCHANGE_NAME, self::ROUTING_KEY);
            $output->writeln('<info>Queue declared and bound</info>');

            // Publish message
            $message = [
                'timestamp' => time(),
                'message' => 'Hello from AMQP test command!',
            ];

            $exchange->publish(
                json_encode($message),
                self::ROUTING_KEY,
                AMQP_NOPARAM,
                [
                    'content_type' => 'application/json',
                    'delivery_mode' => 2, // Persistent message
                ]
            );
            $output->writeln('<info>Message published</info>');

            // Read the message back
            $message = $queue->get(AMQP_AUTOACK);
            if ($message) {
                $output->writeln('<info>Retrieved message:</info>');
                $output->writeln(sprintf('<info>%s</info>', $message->getBody()));

                // Message properties
                $output->writeln('<info>Message properties:</info>');
                $output->writeln(sprintf('<info>- Content Type: %s</info>', $message->getContentType()));
                $output->writeln(sprintf('<info>- Delivery Mode: %d</info>', $message->getDeliveryMode()));
                $output->writeln(sprintf('<info>- Message ID: %s</info>', $message->getMessageId()));
            }

            $connection->disconnect();
            $output->writeln('<info>AMQP test completed successfully!</info>');

            return Command::SUCCESS;
        } catch (\AMQPException $exception) {
            $output->writeln(sprintf('<error>AMQP test failed: %s</error>', $exception->getMessage()));

            return Command::FAILURE;
        }
    }
}
