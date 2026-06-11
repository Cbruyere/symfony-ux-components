<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\DataTable;

final readonly class DataTableState
{
    /**
     * @param list<array{key: string, label: string, sortable?: bool}> $columns
     * @param list<array{name: string, label: string, field?: string, type?: string, placeholder?: string, choices?: list<string>, autoChoices?: bool}> $filters
     * @param array<string, string> $filterValues
     */
    public function __construct(
        public array $columns = [],
        public array $filters = [],
        public array $filterValues = [],
        public string $sort = '',
        public string $direction = 'asc',
    ) {
    }

    public function getDirection(): string
    {
        return 'desc' === $this->direction ? 'desc' : 'asc';
    }

    public function isSortableColumn(string $key): bool
    {
        foreach ($this->columns as $column) {
            if ($column['key'] === $key && true === ($column['sortable'] ?? false)) {
                return true;
            }
        }

        return false;
    }

    public function getFilterValue(string $name): string
    {
        return (string) ($this->filterValues[$name] ?? '');
    }
}
