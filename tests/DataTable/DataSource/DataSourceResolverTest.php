<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\Tests\DataTable\DataSource;

use ChrisDev\UxComponents\DataTable\DataSource\ArrayDataSource;
use ChrisDev\UxComponents\DataTable\DataSource\DataSourceResolver;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class DataSourceResolverTest extends TestCase
{
    public function testItResolvesTheFirstSupportingDataSource(): void
    {
        $arrayDataSource = new ArrayDataSource();
        $resolver = new DataSourceResolver([$arrayDataSource]);

        self::assertSame($arrayDataSource, $resolver->resolve([['name' => 'Admin']]));
    }

    public function testItFailsWhenNoDataSourceSupportsSource(): void
    {
        $resolver = new DataSourceResolver([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No DataTable datasource supports the given source.');

        $resolver->resolve('Unsupported\\Source');
    }
}
