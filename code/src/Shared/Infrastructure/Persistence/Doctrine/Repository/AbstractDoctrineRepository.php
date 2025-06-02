<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Shared\DomainModel\Entity\AggregateRootInterface;
use Shared\Infrastructure\Outbox\Publisher\OutboxPublisherInterface;

abstract readonly class AbstractDoctrineRepository
{
    /**
     * @var ObjectRepository<object>
     */
    protected ObjectRepository $repository;

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected OutboxPublisherInterface $outboxPublisher,
    ) {
        $this->repository = $entityManager->getRepository($this->entityClass());
    }

    /**
     * @throws \RuntimeException
     */
    public function save(AggregateRootInterface $entity, bool $flush = true): void
    {
        $this->entityManager->persist($entity);
        foreach ($entity->releaseEvents() as $event) {
            $this->outboxPublisher->publish($event);
        }

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function remove(AggregateRootInterface $entity, bool $flush = true): void
    {
        $this->entityManager->remove($entity);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }

    public function clear(): void
    {
        $this->entityManager->clear();
    }

    public function beginTransaction(): void
    {
        $this->entityManager->beginTransaction();
    }

    public function commit(): void
    {
        $this->entityManager->commit();
    }

    public function rollback(): void
    {
        $this->entityManager->rollback();
    }

    /**
     * @return class-string
     */
    abstract protected function entityClass(): string;
}
