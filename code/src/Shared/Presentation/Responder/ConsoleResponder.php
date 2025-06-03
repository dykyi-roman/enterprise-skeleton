<?php

declare(strict_types=1);

namespace Shared\Presentation\Responder;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Response;

final class ConsoleResponder extends AbstractResponder
{
    /** @param array<string> $contentTypes */
    #[\Override]
    protected function supportsContentType(array $contentTypes): bool
    {
        return in_array('text/console', $contentTypes, true);
    }

    #[\Override]
    protected function createResponse(ResponderInterface $result): Response
    {
        $json = json_encode($result->payload());

        return new Response(is_string($json) ? $json : '', $result->statusCode(), $result->headers());
    }

    public function renderToConsole(ResponderInterface $result, OutputInterface $output, InputInterface $input): int
    {
        $io = new SymfonyStyle($input, $output);
        /** @var array<string, mixed> $payload */
        $payload = $result->payload();

        // Add title if exists
        if (isset($payload['title']) && is_string($payload['title'])) {
            $io->title($payload['title']);
        }

        // Process messages based on their type
        /** @var array<int, string|array{type: string, content: string}> $messages */
        $messages = is_array($payload['messages'] ?? null) ? $payload['messages'] : [];
        foreach ($messages as $message) {
            // If array with type and content
            if (is_array($message) && isset($message['type'], $message['content']) && is_string($message['type']) && is_string($message['content'])) {
                $this->renderMessageByType($message['type'], $message['content'], $io);
            } elseif (is_string($message)) {
                // Regular message without formatting
                $io->text($message);
            }
        }

        // Display result section if exists
        if (isset($payload['result']) && is_array($payload['result'])) {
            $io->section('Result');

            if (isset($payload['result']['table']) && is_array($payload['result']['table'])) {
                /** @var array<string, mixed> $headers */
                $headers = is_array($payload['result']['table']['headers'] ?? null) ? $payload['result']['table']['headers'] : [];
                /** @var array<int, mixed> $rows */
                $rows = is_array($payload['result']['table']['rows'] ?? null) ? $payload['result']['table']['rows'] : [];
                if (is_array($headers) && is_array($rows)) {
                    $io->table($headers, $rows);
                }
            } elseif (isset($payload['result']['data'])) {
                $value = $payload['result']['data'] ?? null;
                if (is_array($value)) {
                    $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    $io->writeln(is_string($json) ? $json : '');
                } else {
                    $io->text(is_scalar($value) ? (string) $value : '');
                }
            }
        }

        // Show execution time if exists
        if (
            isset($payload['result']) && is_array($payload['result'])
            && isset($payload['result']['performance']) && is_array($payload['result']['performance'])
        ) {
            $perf = $payload['result']['performance'];
            $io->comment(sprintf(
                'Execution time: %s seconds. Memory usage: %s. Peak memory: %s',
                isset($perf['execution_time']) && (is_scalar($perf['execution_time'])) ? (string) $perf['execution_time'] : 'N/A',
                isset($perf['memory_usage']) && is_string($perf['memory_usage']) ? $perf['memory_usage'] : 'N/A',
                isset($perf['peak_memory']) && is_string($perf['peak_memory']) ? $perf['peak_memory'] : 'N/A',
            ));
        }

        // Show execution status
        if (Command::SUCCESS === $result->statusCode()) {
            if (isset($payload['success_message']) && is_string($payload['success_message'])) {
                $io->success($payload['success_message']);
            } else {
                $io->success('The command was successfully executed.');
            }
        } elseif (isset($payload['error']) && is_string($payload['error'])) {
            $io->error($payload['error']);
        } elseif (Command::SUCCESS !== $result->statusCode()) {
            $io->error('An error occurred while executing the command');
        }

        return $result->statusCode();
    }

    /**
     * Renders message based on its type.
     *
     * @param string $type    Message type (info, success, warning, error, note, caution)
     * @param string $message Message content
     */
    private function renderMessageByType(string $type, string $message, SymfonyStyle $io): void
    {
        match ($type) {
            'info' => $io->info($message),
            'success' => $io->success($message),
            'warning' => $io->warning($message),
            'error' => $io->error($message),
            'note' => $io->note($message),
            'caution' => $io->caution($message),
            default => $io->text($message),
        };
    }
}
