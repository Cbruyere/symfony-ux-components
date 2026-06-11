<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\Tests\Twig\Components;

use ChrisDev\UxComponents\DataTable\DataSource\ArrayDataSource;
use ChrisDev\UxComponents\DataTable\DataSource\DataSourceResolver;
use ChrisDev\UxComponents\Twig\Components\DataTable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class DataTableTest extends TestCase
{
    public function testRowsRemainCompatibleWithExistingConfiguration(): void
    {
        $dataTable = $this->createDataTable();
        $dataTable->columns = [
            ['key' => 'name', 'label' => 'Name', 'sortable' => true],
        ];
        $dataTable->rows = [
            ['name' => 'Admin'],
        ];

        self::assertSame([
            ['name' => 'Admin'],
        ], $dataTable->getVisibleRows());
    }

    public function testSourceCanProvideArrayRows(): void
    {
        $dataTable = $this->createDataTable(Request::create('/home', 'GET', [
            'sort' => 'name',
            'direction' => 'asc',
        ]));
        $dataTable->columns = [
            ['key' => 'name', 'label' => 'Name', 'sortable' => true],
        ];
        $dataTable->source = [
            ['name' => 'User'],
            ['name' => 'Admin'],
        ];

        self::assertSame([
            ['name' => 'Admin'],
            ['name' => 'User'],
        ], $dataTable->getVisibleRows());
    }

    private function createDataTable(?Request $request = null): DataTable
    {
        $requestStack = new RequestStack();

        if (null !== $request) {
            $requestStack->push($request);
        }

        return new DataTable(
            $requestStack,
            $this->createMock(UrlGeneratorInterface::class),
            new DataSourceResolver([new ArrayDataSource()]),
        );
    }
}
