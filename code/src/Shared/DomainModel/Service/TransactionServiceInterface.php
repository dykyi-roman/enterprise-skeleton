<?php

declare(strict_types=1);

namespace Shared\DomainModel\Service;

interface TransactionServiceInterface
{
    /**
     * @throws \Throwable
     */
    public function execute(callable $callback): mixed;
}
