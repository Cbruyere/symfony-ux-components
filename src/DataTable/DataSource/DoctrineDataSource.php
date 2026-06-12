<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\DataTable\DataSource;

use ChrisDev\UxComponents\DataTable\DataTableResult;
use ChrisDev\UxComponents\DataTable\DataTableState;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use RuntimeException;

final readonly class DoctrineDataSource implements DataSourceInterface
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
    ) {
    }

    public function supports(mixed $source): bool
    {
        if (!is_string($source) || !class_exists($source)) {
            return false;
        }

        /** @var class-string $source */
        return null !== $this->managerRegistry->getManagerForClass($source);
    }

    public function fetch(mixed $source, DataTableState $state): DataTableResult
    {
        if (!is_string($source) || !class_exists($source)) {
            return new DataTableResult([], 0);
        }

        /** @var class-string $source */
        $entityManager = $this->managerRegistry->getManagerForClass($source);

        if (!$entityManager instanceof EntityManagerInterface) {
            throw new RuntimeException(sprintf('No Doctrine entity manager can handle source "%s".', $source));
        }

        /** @var ClassMetadata<object> $metadata */
        $metadata = $entityManager->getClassMetadata($source);
        $this->assertColumnsCanBeMapped($source, $state, $metadata);

        if ($state->paginate) {
            $totalItems = $this->countItems($entityManager, $source, $state, $metadata);
            $totalPages = $this->getTotalPages($totalItems, $state->getPerPage());
            $currentPage = min($state->getPage(), $totalPages);
        } else {
            $totalItems = null;
            $totalPages = 1;
            $currentPage = 1;
        }

        $queryBuilder = $this->createBaseQueryBuilder($entityManager, $source, $state, $metadata)
            ->select('entity');

        if ($state->isSortableColumn($state->sort) && $metadata->hasField($state->sort)) {
            $queryBuilder->orderBy(sprintf('entity.%s', $state->sort), strtoupper($state->getDirection()));
        }

        if ($state->paginate) {            
            $queryBuilder
                ->setFirstResult(($currentPage - 1) * $state->getPerPage())
                ->setMaxResults($state->getPerPage());
        }

        $result = $queryBuilder->getQuery()->getResult();
        $rows = [];

        if (is_array($result)) {
            foreach ($result as $entity) {
                if (is_object($entity)) {
                    $rows[] = $this->normalizeEntity($entity, $metadata);
                }
            }
        }

        return new DataTableResult(
            $rows,
            $totalItems ?? count($rows),
            $currentPage,
            $state->getPerPage(),
            $totalPages,
        );
    }

    /**
     * @param ClassMetadata<object> $metadata
     * @param class-string $source
     */
    private function createBaseQueryBuilder(
        EntityManagerInterface $entityManager,
        string $source,
        DataTableState $state,
        ClassMetadata $metadata,
    ): \Doctrine\ORM\QueryBuilder {
        $queryBuilder = $entityManager->createQueryBuilder()
            ->from($source, 'entity');

        foreach ($state->filters as $filter) {
            $value = $state->getFilterValue($filter['name']);

            if ('' === $value) {
                continue;
            }

            $field = $filter['field'] ?? $filter['name'];

            if (!$metadata->hasField($field)) {
                continue;
            }

            $parameter = 'filter_'.$filter['name'];
            $queryBuilder
                ->andWhere(sprintf('entity.%s = :%s', $field, $parameter))
                ->setParameter($parameter, $value);
        }

        return $queryBuilder;
    }

    /**
     * @param ClassMetadata<object> $metadata
     * @param class-string $source
     */
    private function countItems(
        EntityManagerInterface $entityManager,
        string $source,
        DataTableState $state,
        ClassMetadata $metadata,
    ): int {
        $count = $this->createBaseQueryBuilder($entityManager, $source, $state, $metadata)
            ->select('COUNT(entity.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return is_numeric($count) ? (int) $count : 0;
    }

    private function getTotalPages(int $totalItems, int $perPage): int
    {
        return max(1, (int) ceil($totalItems / $perPage));
    }

    /**
     * @param ClassMetadata<object> $metadata
     */
    private function assertColumnsCanBeMapped(string $entityClass, DataTableState $state, ClassMetadata $metadata): void
    {
        foreach ($state->columns as $column) {
            if ($metadata->hasField($column['key'])) {
                continue;
            }

            throw new RuntimeException(sprintf(
                'Column "%s" cannot be mapped on entity %s. Use a computed column/value callback or expose a real property.',
                $column['key'],
                $entityClass,
            ));
        }
    }

    /**
     * @param ClassMetadata<object> $metadata
     * @return array<string, mixed>
     */
    private function normalizeEntity(object $entity, ClassMetadata $metadata): array
    {
        $row = [];

        foreach ($metadata->getFieldNames() as $fieldName) {
            $row[$fieldName] = $this->readProperty($entity, $fieldName);
        }

        return $row;
    }

    private function readProperty(object $entity, string $fieldName): mixed
    {
        $method = 'get'.str_replace(' ', '', ucwords(str_replace('_', ' ', $fieldName)));

        if (method_exists($entity, $method)) {
            return $entity->{$method}();
        }

        $method = 'is'.str_replace(' ', '', ucwords(str_replace('_', ' ', $fieldName)));

        if (method_exists($entity, $method)) {
            return $entity->{$method}();
        }

        return null;
    }
}
