<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Persistence\Doctrine\Transaction;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\TransactionIsolationLevel;
use Shared\DomainModel\Service\TransactionServiceInterface;

final readonly class DoctrineTransactionService implements TransactionServiceInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function execute(callable $callback): mixed
    {
        // Checking if we are already in a transaction
        $isAlreadyInTransaction = $this->connection->isTransactionActive();
        if (!$isAlreadyInTransaction) {
            $this->connection->beginTransaction();
        }

        try {
            $result = $callback();

            if (!$isAlreadyInTransaction) {
                $this->connection->commit();
            }

            return $result;
        } catch (\Throwable $exception) {
            // If we are not in a transaction, rollback
            if (!$isAlreadyInTransaction && $this->connection->isTransactionActive()) {
                $this->connection->rollBack();
            }

            throw $exception;
        }
    }

    public function executeWithIsolationLevel(callable $callback, TransactionIsolationLevel $isolationLevel): mixed
    {
        $originalIsolationLevel = $this->connection->getTransactionIsolation();

        try {
            $this->connection->setTransactionIsolation($isolationLevel);

            return $this->execute($callback);
        } finally {
            $this->connection->setTransactionIsolation($originalIsolationLevel);
        }
    }

    public function executeWithRetry(callable $callback, int $maxRetries = 3, int $delay = 100): mixed
    {
        $attempt = 0;

        while ($attempt <= $maxRetries) {
            try {
                return $this->execute($callback);
            } catch (\PDOException $e) {
                if ($this->isDeadlockException($e) && $attempt < $maxRetries) {
                    $attempt++;
                    usleep($delay * 1000 * $attempt);

                    continue;
                }
                throw $e;
            }
        }

        throw new \RuntimeException('Exceeded maximum retry attempts for transaction');
    }

    private function isDeadlockException(\PDOException $e): bool
    {
        // MySQL deadlock error codes
        return in_array($e->getCode(), [1213, 1205], true) ||
            str_contains($e->getMessage(), 'deadlock') ||
            str_contains($e->getMessage(), 'lock timeout');
    }
}
