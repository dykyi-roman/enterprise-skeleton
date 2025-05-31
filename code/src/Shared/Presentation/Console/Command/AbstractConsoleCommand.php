<?php

declare(strict_types=1);

namespace Shared\Presentation\Console\Command;

use Shared\Presentation\Console\Output\ConsoleOutput;
use Shared\Presentation\Responder\ConsoleResponder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractConsoleCommand extends Command
{
    private float $startTime;
    private int $startMemory;

    public function __construct(
        protected readonly ConsoleResponder $responder,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    #[\Override]
    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->startProfiling();

        // We don't measure time and memory if the command failed
        // and wasn't executed through execute

        return parent::run($input, $output);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            // Execute command logic
            $response = $this->executeCommand($input, $output);

            // Add performance information if not already added
            if ($response instanceof ConsoleOutput && null === $response->executionTime()) {
                $performanceInfo = $this->getPerformanceInfo();
                $response = $this->addPerformanceInfo($response, $performanceInfo);
            }

            // Send response
            return $this->responder->renderToConsole($response, $output, $input);
        } catch (\Throwable $exception) {
            $performanceInfo = $this->getPerformanceInfo();

            // Create error response
            $errorResponse = ConsoleOutput::failure(
                [
                    ConsoleOutput::formatMessage('An error occurred while executing the command', 'error'),
                    ConsoleOutput::formatMessage($exception->getMessage(), 'error'),
                ],
                $this->getDescription(),
                sprintf('Error: %s', $exception->getMessage()),
                $performanceInfo['execution_time']
            );

            // Add performance information
            $errorResponse = $this->addPerformanceInfo($errorResponse, $performanceInfo);

            return $this->responder->renderToConsole($errorResponse, $output, $input);
        }
    }

    /**
     * @return ConsoleOutput Command execution result
     */
    abstract protected function executeCommand(InputInterface $input, OutputInterface $output): ConsoleOutput;

    protected function startProfiling(): void
    {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage(true);
    }

    /**
     * @return array{execution_time: float, memory_usage: string, peak_memory: string}
     */
    protected function getPerformanceInfo(): array
    {
        $executionTime = round(microtime(true) - $this->startTime, 4);
        $memoryUsage = $this->formatMemory(memory_get_usage(true) - $this->startMemory);
        $peakMemory = $this->formatMemory(memory_get_peak_usage(true));

        return [
            'execution_time' => $executionTime,
            'memory_usage' => $memoryUsage,
            'peak_memory' => $peakMemory,
        ];
    }

    protected function addPerformanceInfo(ConsoleOutput $response, array $performanceInfo): ConsoleOutput
    {
        $payload = $response->payload();

        // Add performance information to result
        if (!isset($payload['result'])) {
            $payload['result'] = [];
        }

        // Add performance metrics
        $payload['result']['performance'] = [
            'execution_time' => $performanceInfo['execution_time'],
            'memory_usage' => $performanceInfo['memory_usage'],
            'peak_memory' => $performanceInfo['peak_memory'],
        ];

        // Set execution time
        $payload['execution_time'] = $performanceInfo['execution_time'];

        // Create new object with updated data
        return new ConsoleOutput(
            $payload['messages'] ?? [],
            $payload['title'] ?? null,
            $payload['result'] ?? [],
            $payload['success'] ?? true,
            $payload['status_code'] ?? Command::SUCCESS,
            $payload['success_message'] ?? null,
            $payload['error'] ?? null,
            $payload['execution_time'] ?? null,
            $payload['headers'] ?? []
        );
    }

    /**
     * Format memory to readable form (KB, MB, GB).
     */
    protected function formatMemory(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= 1024 ** $pow;

        return round($bytes, 2).' '.$units[$pow];
    }
}
