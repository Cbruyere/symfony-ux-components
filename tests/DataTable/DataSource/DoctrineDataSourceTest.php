<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\Tests\DataTable\DataSource;

use ChrisDev\UxComponents\DataTable\DataSource\DoctrineDataSource;
use ChrisDev\UxComponents\DataTable\DataTableState;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DoctrineDataSourceTest extends TestCase
{
    public function testItFailsWhenColumnCannotBeMappedOnEntity(): void
    {
        $metadata = new ClassMetadata(DemoDoctrineEntity::class);
        $metadata->mapField(['fieldName' => 'name', 'type' => 'string']);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(self::once())
            ->method('getClassMetadata')
            ->with(DemoDoctrineEntity::class)
            ->willReturn($metadata);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry
            ->expects(self::once())
            ->method('getManagerForClass')
            ->with(DemoDoctrineEntity::class)
            ->willReturn($entityManager);

        $dataSource = new DoctrineDataSource($registry);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf(
            'Column "status" cannot be mapped on entity %s.',
            DemoDoctrineEntity::class,
        ));

        $dataSource->fetch(
            DemoDoctrineEntity::class,
            new DataTableState(
                columns: [
                    ['key' => 'name', 'label' => 'Name', 'sortable' => true],
                    ['key' => 'status', 'label' => 'Status', 'sortable' => false],
                ],
            ),
        );
    }

    public function testItAppliesPaginationToDoctrineQuery(): void
    {
        $metadata = new ClassMetadata(DemoDoctrineEntity::class);
        $metadata->mapField(['fieldName' => 'name', 'type' => 'string']);

        $countQuery = $this->createMock(Query::class);
        $countQuery
            ->expects(self::once())
            ->method('getSingleScalarResult')
            ->willReturn(3);

        $dataQuery = $this->createMock(Query::class);
        $dataQuery
            ->expects(self::once())
            ->method('getResult')
            ->willReturn([new DemoDoctrineEntity('Bravo')]);

        $countQueryBuilder = $this->createMock(QueryBuilder::class);
        $countQueryBuilder->method('select')->willReturnSelf();
        $countQueryBuilder->method('from')->willReturnSelf();
        $countQueryBuilder->method('getQuery')->willReturn($countQuery);

        $dataQueryBuilder = $this->createMock(QueryBuilder::class);
        $dataQueryBuilder->method('select')->willReturnSelf();
        $dataQueryBuilder->method('from')->willReturnSelf();
        $dataQueryBuilder->method('orderBy')->willReturnSelf();
        $dataQueryBuilder
            ->expects(self::once())
            ->method('setFirstResult')
            ->with(1)
            ->willReturnSelf();
        $dataQueryBuilder
            ->expects(self::once())
            ->method('setMaxResults')
            ->with(1)
            ->willReturnSelf();
        $dataQueryBuilder->method('getQuery')->willReturn($dataQuery);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getClassMetadata')->willReturn($metadata);
        $entityManager
            ->expects(self::exactly(2))
            ->method('createQueryBuilder')
            ->willReturnOnConsecutiveCalls($countQueryBuilder, $dataQueryBuilder);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($entityManager);

        $result = (new DoctrineDataSource($registry))->fetch(
            DemoDoctrineEntity::class,
            new DataTableState(
                columns: [
                    ['key' => 'name', 'label' => 'Name', 'sortable' => true],
                ],
                sort: 'name',
                direction: 'asc',
                page: 2,
                perPage: 1,
            ),
        );

        self::assertSame([['name' => 'Bravo']], $result->rows);
        self::assertSame(3, $result->totalItems);
        self::assertSame(2, $result->currentPage);
        self::assertSame(1, $result->perPage);
        self::assertSame(3, $result->totalPages);
    }
}

final class DemoDoctrineEntity
{
    public function __construct(
        private readonly string $name = '',
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }
}
