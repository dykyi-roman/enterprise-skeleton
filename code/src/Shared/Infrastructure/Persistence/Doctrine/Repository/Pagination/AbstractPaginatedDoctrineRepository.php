<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Persistence\Doctrine\Repository\Pagination;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Shared\DomainModel\Repository\PaginatedRepositoryInterface;
use Shared\Infrastructure\Persistence\Doctrine\Repository\AbstractDoctrineRepository;

abstract readonly class AbstractPaginatedDoctrineRepository extends AbstractDoctrineRepository implements PaginatedRepositoryInterface
{
    /**
     * Find paginated results using QueryBuilder.
     */
    protected function findPaginated(
        QueryBuilder $queryBuilder,
        PaginationRequest $paginationRequest,
    ): PaginationResult {
        $this->applySorting($queryBuilder, $paginationRequest);
        $this->applyPagination($queryBuilder, $paginationRequest);

        $paginator = new Paginator($queryBuilder->getQuery(), fetchJoinCollection: true);
        $totalItems = count($paginator);

        /** @var array<int, object> $items */
        $items = [];
        foreach ($paginator as $item) {
            if (is_object($item)) {
                $items[] = $item;
            }
        }

        return PaginationResult::create(
            items: $items,
            totalItems: $totalItems,
            paginationRequest: $paginationRequest,
        );
    }

    public function findAllPaginated(PaginationRequest $paginationRequest): PaginationResult
    {
        $queryBuilder = $this->createQueryBuilder();

        return $this->findPaginated($queryBuilder, $paginationRequest);
    }

    /**
     * @param array<string, mixed> $criteria
     */
    public function findByPaginated(array $criteria, PaginationRequest $paginationRequest): PaginationResult
    {
        $queryBuilder = $this->createQueryBuilder();

        $this->applyCriteria($queryBuilder, $criteria);

        return $this->findPaginated($queryBuilder, $paginationRequest);
    }

    protected function createQueryBuilder(string $alias = 'e'): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder()
            ->select($alias)
            ->from($this->entityClass(), $alias);
    }

    protected function applySorting(QueryBuilder $queryBuilder, PaginationRequest $paginationRequest): void
    {
        if (null === $paginationRequest->sortBy) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $sortField = $this->mapSortField($paginationRequest->sortBy);

        if (null === $sortField) {
            return;
        }

        $queryBuilder->orderBy(
            sprintf('%s.%s', $alias, $sortField),
            $paginationRequest->getSortDirection(),
        );
    }

    protected function applyPagination(
        QueryBuilder $queryBuilder,
        PaginationRequest $paginationRequest,
    ): void {
        $queryBuilder
            ->setFirstResult($paginationRequest->getOffset())
            ->setMaxResults($paginationRequest->limit);
    }

    /**
     * @param array<string, mixed> $criteria
     */
    protected function applyCriteria(QueryBuilder $queryBuilder, array $criteria): void
    {
        $alias = $queryBuilder->getRootAliases()[0];

        foreach ($criteria as $field => $value) {
            $parameterName = sprintf('%s_%s', $field, uniqid());

            if (is_array($value)) {
                $queryBuilder
                    ->andWhere(sprintf('%s.%s IN (:%s)', $alias, $field, $parameterName))
                    ->setParameter($parameterName, $value);
            } elseif (null === $value) {
                $queryBuilder->andWhere(sprintf('%s.%s IS NULL', $alias, $field));
            } else {
                $queryBuilder
                    ->andWhere(sprintf('%s.%s = :%s', $alias, $field, $parameterName))
                    ->setParameter($parameterName, $value);
            }
        }
    }

    /**
     * Map external sort field names to entity properties
     * Override in concrete repositories to customize field mapping.
     */
    protected function mapSortField(string $sortField): ?string
    {
        return $sortField;
    }

    /**
     * Get allowed sort fields for this repository
     * Override in concrete repositories to restrict sortable fields.
     *
     * @return array<string>
     */
    protected function getAllowedSortFields(): array
    {
        return [];
    }

    /**
     * Validate if sort field is allowed.
     */
    protected function isSortFieldAllowed(string $sortField): bool
    {
        $allowedFields = $this->getAllowedSortFields();

        if (empty($allowedFields)) {
            return true;
        }

        return in_array($sortField, $allowedFields, true);
    }
}
