<?php

declare(strict_types=1);

namespace Shared\DomainModel\Service;

interface MessageBusInterface
{
    /**
     * @throws \Throwable
     */
    public function dispatch(object $message): mixed;
}
