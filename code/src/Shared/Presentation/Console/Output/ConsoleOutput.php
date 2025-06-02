<?php

declare(strict_types=1);

namespace Shared\Presentation\Console\Output;

use Shared\Presentation\Responder\ResponderInterface;
use Symfony\Component\Console\Command\Command;

final readonly class ConsoleOutput implements ResponderInterface
{
    /**
     * @var array<int, string|array{type: string, content: string}>
     */
    private array $messages;
    private ?string $title;
    /**
     * @var array<string, mixed>
     */
    private array $result;
    private bool $success;
    private int $statusCode;
    private ?string $successMessage;
    private ?string $errorMessage;
    private ?float $executionTime;
    /**
     * @var array<string, string>
     */
    private array $headers;

    /**
     * @param array<int, string|array{type: string, content: string}> $messages Array of messages or formatted messages
     * @param array<string, mixed>                                    $result   Result data
     * @param array<string, string>                                   $headers  Response headers
     */
    public function __construct(
        array $messages = [],
        ?string $title = null,
        array $result = [],
        bool $success = true,
        int $statusCode = Command::SUCCESS,
        ?string $successMessage = null,
        ?string $errorMessage = null,
        ?float $executionTime = null,
        array $headers = [],
    ) {
        $this->messages = $messages;
        $this->title = $title;
        $this->result = $result;
        $this->success = $success;
        $this->statusCode = $statusCode;
        $this->successMessage = $successMessage;
        $this->errorMessage = $errorMessage;
        $this->executionTime = $executionTime;
        $this->headers = $headers;
    }

    #[\Override]
    public function respond(): self
    {
        return $this;
    }

    #[\Override]
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

    #[\Override]
    public function statusCode(): int
    {
        return $this->statusCode;
    }

    #[\Override]
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
     * @param array<int, string|array{type: string, content: string}> $messages
     * @param array<string, mixed>                                    $result
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
     * @param array<int, string|array{type: string, content: string}> $messages
     * @param string|null                                             $errorMessage Error message to display
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
