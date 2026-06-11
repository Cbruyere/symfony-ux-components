<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\Tests\DataTable\DataSource;

use ChrisDev\UxComponents\DataTable\DataSource\ArrayDataSource;
use ChrisDev\UxComponents\DataTable\DataTableState;
use PHPUnit\Framework\TestCase;

final class ArrayDataSourceTest extends TestCase
{
    public function testItFiltersAndSortsArrayRows(): void
    {
        $dataSource = new ArrayDataSource();
        $state = new DataTableState(
            columns: [
                ['key' => 'name', 'label' => 'Name', 'sortable' => true],
                ['key' => 'status', 'label' => 'Status', 'sortable' => true],
            ],
            filters: [
                ['name' => 'status', 'label' => 'Status', 'field' => 'status', 'type' => 'select'],
            ],
            filterValues: ['status' => 'Active'],
            sort: 'name',
            direction: 'desc',
        );

        $result = $dataSource->fetch([
            ['name' => 'Alice', 'status' => 'Active'],
            ['name' => 'Bob', 'status' => 'Inactive'],
            ['name' => 'Charlie', 'status' => 'Active'],
        ], $state);

        self::assertSame([
            ['name' => 'Charlie', 'status' => 'Active'],
            ['name' => 'Alice', 'status' => 'Active'],
        ], $result->rows);
        self::assertSame(2, $result->totalItems);
    }

    public function testItPaginatesAfterFilteringAndSorting(): void
    {
        $dataSource = new ArrayDataSource();
        $state = new DataTableState(
            columns: [
                ['key' => 'name', 'label' => 'Name', 'sortable' => true],
            ],
            filters: [
                ['name' => 'status', 'label' => 'Status', 'field' => 'status', 'type' => 'select'],
            ],
            filterValues: ['status' => 'Active'],
            sort: 'name',
            direction: 'asc',
            page: 2,
            perPage: 2,
        );

        $result = $dataSource->fetch([
            ['name' => 'Delta', 'status' => 'Active'],
            ['name' => 'Echo', 'status' => 'Inactive'],
            ['name' => 'Alpha', 'status' => 'Active'],
            ['name' => 'Charlie', 'status' => 'Active'],
            ['name' => 'Bravo', 'status' => 'Active'],
        ], $state);

        self::assertSame([
            ['name' => 'Charlie', 'status' => 'Active'],
            ['name' => 'Delta', 'status' => 'Active'],
        ], $result->rows);
        self::assertSame(4, $result->totalItems);
        self::assertSame(2, $result->currentPage);
        self::assertSame(2, $result->perPage);
        self::assertSame(2, $result->totalPages);
    }
}
