<?php

declare(strict_types=1);

namespace Shared\Application\Exception;

/**
 * @template T of \BackedEnum
 */
class ApplicationException extends \RuntimeException
{
    /**
     * @var non-empty-string
     */
    public string $useCaseName;

    /**
     * @param non-empty-string $classPath
     * @param T $errorCode
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
        if ($useCasesIndex === false) {
            throw new \RuntimeException('Invalid command namespace structure');
        }

        $this->useCaseName = $namespaceParts[$useCasesIndex + 1];

        parent::__construct($message, 0, $previous);
    }
}