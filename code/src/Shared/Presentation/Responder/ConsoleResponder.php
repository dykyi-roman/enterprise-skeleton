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
        return new Response(json_encode($result->payload()), $result->statusCode(), $result->headers());
    }

    public function renderToConsole(ResponderInterface $result, OutputInterface $output, InputInterface $input): int
    {
        $io = new SymfonyStyle($input, $output);
        $payload = $result->payload();

        // Add title if exists
        if (isset($payload['title'])) {
            $io->title($payload['title']);
        }

        // Process messages based on their type
        if (isset($payload['messages']) && is_array($payload['messages'])) {
            foreach ($payload['messages'] as $message) {
                // If array with type and content
                if (is_array($message) && isset($message['type']) && isset($message['content'])) {
                    $this->renderMessageByType($message['type'], $message['content'], $io);
                } elseif (is_string($message)) {
                    // Regular message without formatting
                    $io->text($message);
                }
            }
        }

        // Display result section if exists
        if (isset($payload['result']) && is_array($payload['result'])) {
            $io->section('Result');

            if (isset($payload['result']['table']) && is_array($payload['result']['table'])) {
                // If table data exists
                $headers = $payload['result']['table']['headers'] ?? [];
                $rows = $payload['result']['table']['rows'] ?? [];
                $io->table($headers, $rows);
            } elseif (isset($payload['result']['data'])) {
                // If other data exists
                if (is_array($payload['result']['data'])) {
                    $io->writeln(json_encode($payload['result']['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                } else {
                    $io->text((string) $payload['result']['data']);
                }
            }
        }

        // Show execution time if exists
        if (isset($payload['result']['performance'])) {
            $io->comment(sprintf('Execution time: %s seconds. Memory usage: %s. Peak memory: %s',
                $payload['result']['performance']['execution_time'],
                $payload['result']['performance']['memory_usage'],
                $payload['result']['performance']['peak_memory'],
            ));
        }

        // Show execution status
        if (Command::SUCCESS === $result->statusCode()) {
            if (isset($payload['success_message'])) {
                $io->success($payload['success_message']);
            } else {
                $io->success('The command was successfully executed.');
            }
        } elseif (isset($payload['error'])) {
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
