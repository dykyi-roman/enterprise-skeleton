<?php

declare(strict_types=1);

namespace App\Healthcheck\Presentation\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:healthcheck:metrics',
    description: 'Send test metrics to Prometheus via Pushgateway',
)]
final class GraphanaHealthcheckCommand extends Command
{
    private const PUSH_GATEWAY_HOST = 'es-pushgateway:9091';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Prepare metric data in Prometheus format
        $metrics = [];

        // Counter metric with HELP and TYPE
        $metrics[] = '# HELP test_counter A test counter metric';
        $metrics[] = '# TYPE test_counter counter';
        $metrics[] = 'test_counter 1';

        // Gauge metric with HELP and TYPE
        $metrics[] = '# HELP test_gauge A test gauge metric';
        $metrics[] = '# TYPE test_gauge gauge';
        $metrics[] = sprintf('test_gauge %d', random_int(1, 100));

        $metricsData = implode("\n", $metrics)."\n"; // Add final newline

        // Initialize cURL
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => sprintf('http://%s/metrics/job/test_job', self::PUSH_GATEWAY_HOST),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $metricsData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: text/plain'],
            CURLOPT_TIMEOUT => 5,
        ]);

        // Send request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $output->writeln('<error>Curl error: '.$error.'</error>');

            return Command::FAILURE;
        }

        if (200 === $httpCode) {
            $output->writeln('<info>Metrics sent successfully!</info>');

            return Command::SUCCESS;
        }

        $output->writeln('<error>Failed to send metrics. HTTP Code: '.$httpCode.'</error>');
        $output->writeln('<error>Response: '.$response.'</error>');

        return Command::FAILURE;
    }
}
