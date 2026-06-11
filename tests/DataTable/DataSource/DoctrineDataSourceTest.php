<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\Tests\DataTable\DataSource;

use ChrisDev\UxComponents\DataTable\DataSource\DoctrineDataSource;
use ChrisDev\UxComponents\DataTable\DataTableState;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
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
}

final class DemoDoctrineEntity
{
}
