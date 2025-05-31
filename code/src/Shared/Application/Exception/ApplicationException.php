<?php

declare(strict_types=1);

namespace Shared\Application\Exception;

/**
 * @template T of \BackedEnum
 */
class ApplicationException extends \RuntimeException
{
    public string $useCaseName;

    /**
     * @param non-empty-string     $classPath
     * @param string               $message
     * @param array<string, mixed> $details
     */
    public function __construct(
        string $classPath,
        public readonly \BackedEnum $errorCode,
        public $message,
        public readonly array $details = [],
        ?\Throwable $previous = null,
    ) {
        $namespaceParts = explode('\\', $classPath);
        $useCasesIndex = array_search('UseCases', $namespaceParts, true);
        if (false === $useCasesIndex) {
            throw new \RuntimeException('Invalid command namespace structure');
        }

        $this->useCaseName = $namespaceParts[$useCasesIndex + 1];

        parent::__construct($message, 0, $previous);
    }
}
