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
    private ConsoleResponder $responder;

    public function __construct(
        ?string $name = null,
    ) {
        $this->responder = new ConsoleResponder();

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
            $performanceInfo = $this->getPerformanceInfo();
            $response = $this->addPerformanceInfo($response, $performanceInfo);

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
        $memoryUsage = $this->formatBytes(memory_get_usage(true) - $this->startMemory);
        $peakMemory = $this->formatBytes(memory_get_peak_usage(true));

        return [
            'execution_time' => $executionTime,
            'memory_usage' => $memoryUsage,
            'peak_memory' => $peakMemory,
        ];
    }

    /**
     * @param array<string, float|string> $performanceInfo
     */
    protected function addPerformanceInfo(ConsoleOutput $response, array $performanceInfo): ConsoleOutput
    {
        $payload = $response->payload();

        // Add performance information to result
        if (!isset($payload['result']) || !is_array($payload['result'])) {
            $payload['result'] = [];
        }
        $payload['result']['performance'] = [
            'execution_time' => $performanceInfo['execution_time'],
            'memory_usage' => $performanceInfo['memory_usage'],
            'peak_memory' => $performanceInfo['peak_memory'],
        ];

        $payload['execution_time'] = $payload['result']['performance']['execution_time'];

        // Приведение типов для конструктора ConsoleOutput
        $messages = [];
        $messagesRaw = $payload['messages'] ?? [];
        if (!is_array($messagesRaw)) {
            $messagesRaw = [];
        }
        foreach ($messagesRaw as $msg) {
            if (is_string($msg)) {
                $messages[] = $msg;
            } elseif (is_array($msg) && isset($msg['type'], $msg['content']) && is_string($msg['type']) && is_string(
                $msg['content']
            )) {
                $messages[] = [
                    'type' => $msg['type'],
                    'content' => $msg['content'],
                ];
            }
        }
        $title = (isset($payload['title']) && (is_string($payload['title']) || is_null(
            $payload['title']
        ))) ? $payload['title'] : null;
        $result = [];
        $resultRaw = $payload['result'];
        if (!is_array($resultRaw)) {
            $resultRaw = [];
        }
        foreach ($resultRaw as $k => $v) {
            if (is_string($k)) {
                $result[$k] = $v;
            }
        }
        $success = (isset($payload['success']) && is_bool($payload['success'])) ? $payload['success'] : true;
        $statusCode = (isset($payload['status_code']) && is_int(
            $payload['status_code']
        )) ? $payload['status_code'] : Command::SUCCESS;
        $successMessage = (isset($payload['success_message']) && (is_string($payload['success_message']) || is_null(
            $payload['success_message']
        ))) ? $payload['success_message'] : null;
        $errorMessage = (isset($payload['error']) && (is_string($payload['error']) || is_null(
            $payload['error']
        ))) ? $payload['error'] : null;
        $executionTime = (isset($payload['execution_time']) && (is_float($payload['execution_time']) || is_int(
            $payload['execution_time']
        ))) ? (float) $payload['execution_time'] : null;
        /** @var array<string, string> $headers */
        $headers = [];
        $headersRaw = $payload['headers'] ?? [];
        if (!is_array($headersRaw)) {
            $headersRaw = [];
        }
        foreach ($headersRaw as $k => $v) {
            if (is_string($k) && is_string($v)) {
                $headers[$k] = $v;
            }
        }

        return new ConsoleOutput(
            $messages,
            $title,
            $result,
            $success,
            $statusCode,
            $successMessage,
            $errorMessage,
            $executionTime,
            $headers
        );
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow = floor((float) ($bytes ? log((float) $bytes) : 0) / log(1024));
        $pow = (int) min($pow, count($units) - 1);
        $bytes = (float) $bytes / (1024 ** $pow);

        return round($bytes, 2).' '.$units[$pow];
    }
}
