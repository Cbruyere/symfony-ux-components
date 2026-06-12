<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\DataTable\DataSource;

use ChrisDev\UxComponents\DataTable\DataTableResult;
use ChrisDev\UxComponents\DataTable\DataTableState;

final class ArrayDataSource implements DataSourceInterface
{
    public function supports(mixed $source): bool
    {
        return is_array($source);
    }

    public function fetch(mixed $source, DataTableState $state): DataTableResult
    {
        if (!is_array($source)) {
            return new DataTableResult([], 0);
        }

        $rows = $this->normalizeRows($source);
        $rows = $this->applyFilters($rows, $state);
        $rows = $this->applySorting($rows, $state);
        $totalItems = count($rows);

        if (!$state->paginate) {
            return new DataTableResult($rows, $totalItems, 1, $state->getPerPage(), 1);
        }

        $totalPages = $this->getTotalPages($totalItems, $state->getPerPage());
        $currentPage = min($state->getPage(), $totalPages);
        $offset = ($currentPage - 1) * $state->getPerPage();
        $rows = array_slice($rows, $offset, $state->getPerPage());

        return new DataTableResult($rows, $totalItems, $currentPage, $state->getPerPage(), $totalPages);
    }

    /**
     * @param array<mixed> $source
     * @return list<array<string, mixed>>
     */
    private function normalizeRows(array $source): array
    {
        $rows = [];

        foreach ($source as $row) {
            if (!is_array($row)) {
                continue;
            }

            $normalizedRow = [];

            foreach ($row as $key => $value) {
                if (is_string($key)) {
                    $normalizedRow[$key] = $value;
                }
            }

            $rows[] = $normalizedRow;
        }

        return $rows;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private function applyFilters(array $rows, DataTableState $state): array
    {
        foreach ($state->filters as $filter) {
            $value = $state->getFilterValue($filter['name']);

            if ('' === $value) {
                continue;
            }

            $field = $filter['field'] ?? $filter['name'];
            $type = $filter['type'] ?? 'select';

            $rows = array_values(array_filter(
                $rows,
                static fn (array $row): bool => 'select' === $type
                    && isset($row[$field])
                    && is_scalar($row[$field])
                    && (string) $row[$field] === $value,
            ));
        }

        return $rows;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private function applySorting(array $rows, DataTableState $state): array
    {
        if (!$state->isSortableColumn($state->sort)) {
            return $rows;
        }

        usort($rows, static function (array $left, array $right) use ($state): int {
            $comparison = strnatcasecmp(
                self::getCellValue($left, $state->sort),
                self::getCellValue($right, $state->sort),
            );

            return 'desc' === $state->getDirection() ? -$comparison : $comparison;
        });

        return $rows;
    }

    /** @param array<string, mixed> $row */
    private static function getCellValue(array $row, string $key): string
    {
        $value = $row[$key] ?? '';

        if (is_scalar($value)) {
            return (string) $value;
        }

        return '';
    }

    private function getTotalPages(int $totalItems, int $perPage): int
    {
        return max(1, (int) ceil($totalItems / $perPage));
    }
}
