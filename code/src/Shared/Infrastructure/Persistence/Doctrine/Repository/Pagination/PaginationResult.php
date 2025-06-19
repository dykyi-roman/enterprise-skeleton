<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Persistence\Doctrine\Repository\Pagination;

final readonly class PaginationResult
{
    /**
     * @param array<int, object> $items
     */
    public function __construct(
        public array $items,
        public int $totalItems,
        public int $currentPage,
        public int $itemsPerPage,
        public int $totalPages,
    ) {
    }

    /**
     * @param array<int, object> $items
     */
    public static function create(
        array $items,
        int $totalItems,
        PaginationRequest $paginationRequest,
    ): self {
        $totalPages = (int) ceil($totalItems / $paginationRequest->limit);

        return new self(
            items: $items,
            totalItems: $totalItems,
            currentPage: $paginationRequest->page,
            itemsPerPage: $paginationRequest->limit,
            totalPages: $totalPages,
        );
    }

    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->totalPages;
    }

    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    public function getNextPage(): ?int
    {
        return $this->hasNextPage() ? $this->currentPage + 1 : null;
    }

    public function getPreviousPage(): ?int
    {
        return $this->hasPreviousPage() ? $this->currentPage - 1 : null;
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function getItemsCount(): int
    {
        return count($this->items);
    }
}
