<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\DataTable\DataSource;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class DataSourceResolver
{
    /**
     * @param iterable<DataSourceInterface> $dataSources
     */
    public function __construct(
        #[AutowireIterator('chrisdev_ux_components.data_table_data_source')]
        private iterable $dataSources,
    ) {
    }

    public function resolve(mixed $source): DataSourceInterface
    {
        foreach ($this->dataSources as $dataSource) {
            if ($dataSource->supports($source)) {
                return $dataSource;
            }
        }

        throw new InvalidArgumentException('No DataTable datasource supports the given source.');
    }
}
