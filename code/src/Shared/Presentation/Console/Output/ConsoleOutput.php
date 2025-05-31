<?php

declare(strict_types=1);

namespace Shared\Presentation\Console\Output;

use Shared\Presentation\Responder\ResponderInterface;
use Symfony\Component\Console\Command\Command;

final readonly class ConsoleOutput implements ResponderInterface
{
    /**
     * @param array<int, string|array<string, string>> $messages Array of messages or formatted messages
     * @param array<string, mixed>                     $result   Result data
     * @param array<string, string>                    $headers  Response headers
     */
    public function __construct(
        private array $messages = [],
        private ?string $title = null,
        private array $result = [],
        private bool $success = true,
        private int $statusCode = Command::SUCCESS,
        private ?string $successMessage = null,
        private ?string $errorMessage = null,
        private ?float $executionTime = null,
        private array $headers = [],
    ) {
    }

    public function respond(): self
    {
        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $payload = [
            'messages' => $this->messages,
            'success' => $this->success,
            'status_code' => $this->statusCode,
        ];

        if (null !== $this->title) {
            $payload['title'] = $this->title;
        }

        if (!empty($this->result)) {
            $payload['result'] = $this->result;
        }

        if (null !== $this->successMessage) {
            $payload['success_message'] = $this->successMessage;
        }

        if (null !== $this->errorMessage) {
            $payload['error'] = $this->errorMessage;
        }

        if (null !== $this->executionTime) {
            $payload['execution_time'] = $this->executionTime;
        }

        return $payload;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return $this->headers;
    }

    public function executionTime(): ?float
    {
        return $this->executionTime;
    }

    /**
     * @param array<int, string|array<string, string>> $messages
     * @param array<string, mixed>                     $result   Optional result data
     */
    public static function success(
        array $messages = [],
        ?string $title = null,
        array $result = [],
        ?string $successMessage = null,
        ?float $executionTime = null,
    ): self {
        return new self(
            $messages,
            $title,
            $result,
            true,
            Command::SUCCESS,
            $successMessage,
            null,
            $executionTime
        );
    }

    /**
     * @param array<int, string|array<string, string>> $messages
     * @param string|null                              $errorMessage Error message to display
     */
    public static function failure(
        array $messages = [],
        ?string $title = null,
        ?string $errorMessage = null,
        ?float $executionTime = null,
    ): self {
        return new self(
            $messages,
            $title,
            [],
            false,
            Command::FAILURE,
            null,
            $errorMessage,
            $executionTime
        );
    }

    /**
     * @return array{type: string, content: string}
     */
    public static function formatMessage(string $content, string $type = 'info'): array
    {
        return ['type' => $type, 'content' => $content];
    }
}
