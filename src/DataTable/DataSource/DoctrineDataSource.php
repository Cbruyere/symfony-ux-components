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
        return is_string($source)
            && class_exists($source)
            && null !== $this->managerRegistry->getManagerForClass($source);
    }

    public function fetch(mixed $source, DataTableState $state): DataTableResult
    {
        if (!is_string($source)) {
            return new DataTableResult([], 0);
        }

        $entityManager = $this->managerRegistry->getManagerForClass($source);

        if (!$entityManager instanceof EntityManagerInterface) {
            throw new RuntimeException(sprintf('No Doctrine entity manager can handle source "%s".', $source));
        }

        /** @var ClassMetadata<object> $metadata */
        $metadata = $entityManager->getClassMetadata($source);
        $this->assertColumnsCanBeMapped($source, $state, $metadata);

        $queryBuilder = $entityManager->createQueryBuilder()
            ->select('entity')
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

        if ($state->isSortableColumn($state->sort) && $metadata->hasField($state->sort)) {
            $queryBuilder->orderBy(sprintf('entity.%s', $state->sort), strtoupper($state->getDirection()));
        }

        $entities = $queryBuilder->getQuery()->getResult();
        $rows = array_map(
            fn (object $entity): array => $this->normalizeEntity($entity, $metadata),
            array_filter($entities, 'is_object'),
        );

        return new DataTableResult(array_values($rows), count($rows));
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
