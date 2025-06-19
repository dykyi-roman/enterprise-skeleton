<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Persistence\Doctrine\Repository\Pagination;

final readonly class PaginationRequest
{
    public function __construct(
        public int $page = 1,
        public int $limit = 20,
        public ?string $sortBy = null,
        public string $sortDirection = 'ASC',
    ) {
        if ($this->page < 1) {
            throw new \InvalidArgumentException('Page must be greater than 0');
        }

        if ($this->limit < 1 || $this->limit > 100) {
            throw new \InvalidArgumentException('Limit must be between 1 and 100');
        }

        if (!in_array(strtoupper($this->sortDirection), ['ASC', 'DESC'], true)) {
            throw new \InvalidArgumentException('Sort direction must be ASC or DESC');
        }
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->limit;
    }

    public function getSortDirection(): string
    {
        return strtoupper($this->sortDirection);
    }
}
