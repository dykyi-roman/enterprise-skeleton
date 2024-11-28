<?php

declare(strict_types=1);

namespace App\Healthcheck\Presentation\Console;

use RdKafka\Conf;
use RdKafka\Producer;
use RdKafka\KafkaConsumer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:healthcheck:kafka',
    description: 'Check Kafka connection and basic functionality'
)]
final class KafkaHealthcheckCommand extends Command
{
    private const string TEST_TOPIC = 'healthcheck_topic';
    private const string TEST_MESSAGE = 'Health check message';
    private const string KAFKA_BROKER = 'es-kafka:9092';
    private const string GROUP_ID = 'healthcheck_group';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Kafka Health Check');

        try {
            // Producer configuration
            $conf = new Conf();
            $conf->set('metadata.broker.list', self::KAFKA_BROKER);
            $conf->set('socket.timeout.ms', '5000');
            $conf->set('queue.buffering.max.ms', '1000');

            // Create producer
            $producer = new Producer($conf);
            
            // Produce message
            $topic = $producer->newTopic(self::TEST_TOPIC);
            $topic->produce(RD_KAFKA_PARTITION_UA, 0, self::TEST_MESSAGE);
            $producer->flush(1000);

            $io->success('Successfully produced test message');

            // Consumer configuration
            $conf = new Conf();
            $conf->set('group.id', self::GROUP_ID);
            $conf->set('metadata.broker.list', self::KAFKA_BROKER);
            $conf->set('auto.offset.reset', 'earliest');
            $conf->set('socket.timeout.ms', '5000');
            $conf->set('fetch.wait.max.ms', '100');

            // Create consumer
            $consumer = new KafkaConsumer($conf);
            $consumer->subscribe([self::TEST_TOPIC]);

            // Try to consume the message
            $message = $consumer->consume(5000);
            
            if ($message->payload === self::TEST_MESSAGE) {
                $io->success('Successfully consumed test message');
                return Command::SUCCESS;
            }

            $io->error('Failed to consume test message');
            return Command::FAILURE;

        } catch (\Exception $exception) {
            $io->error('Kafka health check failed: ' . $exception->getMessage());
            return Command::FAILURE;
        }
    }
}
