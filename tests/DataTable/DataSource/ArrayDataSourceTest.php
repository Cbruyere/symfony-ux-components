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
}
