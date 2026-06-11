<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\DataTable\DataSource;

use ChrisDev\UxComponents\DataTable\DataTableResult;
use ChrisDev\UxComponents\DataTable\DataTableState;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('chrisdev_ux_components.data_table_data_source')]
interface DataSourceInterface
{
    public function supports(mixed $source): bool;

    public function fetch(mixed $source, DataTableState $state): DataTableResult;
}
