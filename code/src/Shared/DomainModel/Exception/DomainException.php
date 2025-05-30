<?php

declare(strict_types=1);

namespace Shared\DomainModel\Exception;

/**
 * @template T of \BackedEnum
 */
abstract class DomainException extends \DomainException implements \JsonSerializable
{
    /**
     * @param T $errorCode
     */
    public function __construct(
        protected readonly \BackedEnum $errorCode,
        string $message,
        public array $context = [] {
            get {
                return $this->context;
            }
        },
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * @return T
     */
    public function getErrorCode(): \BackedEnum
    {
        return $this->errorCode;
    }

    public function jsonSerialize(): array
    {
       return [
           'errorCode' => $this->errorCode,
           'context' => $this->context,
       ];
    }
}