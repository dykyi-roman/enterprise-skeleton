<?php

declare(strict_types=1);

namespace Shared\DomainModel\Repository;

use Shared\Infrastructure\Persistence\Doctrine\Repository\Pagination\PaginationRequest;
use Shared\Infrastructure\Persistence\Doctrine\Repository\Pagination\PaginationResult;

interface PaginatedRepositoryInterface
{
    public function findAllPaginated(PaginationRequest $paginationRequest): PaginationResult;

    /**
     * @param array<string, mixed> $criteria
     */
    public function findByPaginated(array $criteria, PaginationRequest $paginationRequest): PaginationResult;
}
