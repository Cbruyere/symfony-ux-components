<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\DataTable;

final readonly class DataTableResult
{
    /**
     * @param list<array<string, mixed>> $rows
     */
    public function __construct(
        public array $rows,
        public int $totalItems,
        public int $currentPage = 1,
        public int $perPage = 10,
        public int $totalPages = 1,
    ) {
    }
}
