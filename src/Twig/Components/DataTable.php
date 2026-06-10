<?php

namespace ChrisDev\UxComponents\Twig\Components;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;


#[AsLiveComponent(
    name: 'DataTable',
    template: '@ChrisDevUxComponents/components/DataTable.html.twig'
)]
final class DataTable
{
    use DefaultActionTrait;

    #[LiveProp]
    public string $title = '';

    #[LiveProp]
    public string $id = 'data-table';

    /** @var list<array{key: string, label: string, sortable?: bool}> */
    #[LiveProp]
    public array $columns = [];

    /** @var list<array<string, mixed>> */
    #[LiveProp]
    public array $rows = [];

    /** @var list<array{label: string, route: string, icon?: string, variant?: string, name?: string, params?: array<string, string>}> */
    #[LiveProp]
    public array $actions = [];

    /** @var list<array{name: string, label: string, field?: string, type?: string, placeholder?: string, choices?: list<string>, autoChoices?: bool}> */
    #[LiveProp]
    public array $filters = [];

    /** @var array{title: string, message: string, icon: string} */
    #[LiveProp]
    public array $emptyState = [
        'title' => 'Aucune donnée disponible.',
        'message' => 'Les informations apparaitront ici lorsque des lignes seront disponibles.',
        'icon' => 'bi:inbox',
    ];

    /** @var array<string, string> */
    #[LiveProp(writable: true)]
    public array $filterValues = [];

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /** @return list<array{key: string, label: string, sortable: bool}> */
    public function getColumns(): array
    {
        return array_map(
            static fn (array $column): array => [
                'key' => $column['key'],
                'label' => $column['label'],
                'sortable' => $column['sortable'] ?? false,
            ],
            $this->columns,
        );
    }

    /** @return list<array<string, mixed>> */
    public function getVisibleRows(): array
    {
        $rows = $this->rows;
        $rows = $this->applyFilters($rows);

        return $this->applySorting($rows);
    }

    /** @return list<array{label: string, route: string, icon: string, variant: string, name: string, params: array<string, string>}> */
    public function getActions(): array
    {
        return array_map(
            static fn (array $action): array => [
                'label' => $action['label'],
                'route' => $action['route'],
                'icon' => $action['icon'] ?? 'bi:arrow-right',
                'variant' => $action['variant'] ?? 'secondary',
                'name' => $action['name'] ?? strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', $action['label'])),
                'params' => $action['params'] ?? [],
            ],
            $this->actions,
        );
    }

    /** @return list<array{name: string, label: string, field: string, type: string, placeholder: string, choices: list<string>, value: string}> */
    public function getVisibleFilters(): array
    {
        return array_map(
            fn (array $filter): array => [
                'name' => $filter['name'],
                'label' => $filter['label'],
                'field' => $filter['field'] ?? $filter['name'],
                'type' => $filter['type'] ?? 'select',
                'placeholder' => $filter['placeholder'] ?? '',
                'choices' => $this->getFilterChoices($filter),
                'value' => $this->getFilterValue($filter['name']),
            ],
            $this->filters,
        );
    }

    /** @param array{key: string} $column */
    public function getSortUrl(array $column): string
    {
        $request = $this->getRequest();
        $query = $this->getCleanQuery();
        $query['sort'] = $column['key'];
        $query['direction'] = $this->getDirectionForColumn($column);

        return ($request?->getPathInfo() ?? '/home').'?'.http_build_query($query);
    }

    /** @param array{key: string} $column */
    public function getDirectionForColumn(array $column): string
    {
        if ($this->getCurrentSort() !== $column['key']) {
            return 'asc';
        }

        return 'asc' === $this->getCurrentDirection() ? 'desc' : 'asc';
    }

    /** @param array{key: string} $column */
    public function getSortIcon(array $column): string
    {
        if ($this->getCurrentSort() !== $column['key']) {
            return 'bi:arrow-down-up';
        }

        return 'asc' === $this->getCurrentDirection() ? 'bi:sort-alpha-down' : 'bi:sort-alpha-up';
    }

    /** @param array{variant: string} $action */
    public function getActionClasses(array $action): string
    {
        return match ($action['variant']) {
            'primary' => 'border-blue-600 bg-blue-600 text-white hover:bg-blue-500 focus:bg-blue-700',
            'danger' => 'border-red-700 bg-red-950 text-red-200 hover:bg-red-900 focus:bg-red-900',
            default => 'border-slate-700 bg-slate-900 text-slate-200 hover:bg-slate-800 focus:bg-slate-800',
        };
    }

    /** @param array{type: string} $filter */
    public function getFilterClasses(array $filter): string
    {
        return 'block w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500';
    }

    /** @param array{name: string} $filter */
    public function getFilterInputName(array $filter): string
    {
        return 'filterValues.'.$filter['name'];
    }

    /** @return array{title: string, message: string, icon: string} */
    public function getEmptyState(): array
    {
        return $this->emptyState;
    }

    /**
     * @param array{params: array<string, string>, route: string} $action
     * @param array<string, mixed> $row
     */
    public function getActionUrl(array $action, array $row): string
    {
        $parameters = [];

        foreach ($action['params'] as $routeParameter => $rowKey) {
            if (isset($row[$rowKey]) && is_scalar($row[$rowKey])) {
                $parameters[$routeParameter] = (string) $row[$rowKey];
            }
        }

        return $this->urlGenerator->generate($action['route'], $parameters);
    }

    /** @param array<string, mixed> $row */
    public function getCellValue(array $row, string $key): string
    {
        $value = $row[$key] ?? '';

        if (is_scalar($value)) {
            return (string) $value;
        }

        return '';
    }

    private function getCurrentSort(): string
    {
        return $this->getQueryValue('sort');
    }

    private function getCurrentDirection(): string
    {
        return 'desc' === $this->getQueryValue('direction') ? 'desc' : 'asc';
    }

    private function getQueryValue(string $name): string
    {
        $value = $this->getRequest()?->query->get($name);

        return is_scalar($value) ? (string) $value : '';
    }

    private function getFilterValue(string $name): string
    {
        return (string) ($this->filterValues[$name] ?? '');
    }

    /**
     * @param array{field?: string, name: string, choices?: list<string>, autoChoices?: bool} $filter
     * @return list<string>
     */
    private function getFilterChoices(array $filter): array
    {
        if (true !== ($filter['autoChoices'] ?? false)) {
            return $filter['choices'] ?? [];
        }

        $field = $filter['field'] ?? $filter['name'];
        $choices = [];

        foreach ($this->rows as $row) {
            if (!isset($row[$field]) || !is_scalar($row[$field])) {
                continue;
            }

            $value = (string) $row[$field];

            if ('' !== $value) {
                $choices[$value] = $value;
            }
        }

        natcasesort($choices);

        return array_values($choices);
    }

    private function getRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    /** @return array<string, mixed> */
    private function getCleanQuery(): array
    {
        $query = $this->getRequest()?->query->all() ?? [];

        foreach ($query as $key => $value) {
            if ('' === $value || null === $value || [] === $value) {
                unset($query[$key]);
            }
        }

        return $query;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private function applyFilters(array $rows): array
    {
        foreach ($this->getVisibleFilters() as $filter) {
            if ('' === $filter['value']) {
                continue;
            }

            $rows = array_values(array_filter(
                $rows,
                fn (array $row): bool => $this->rowMatchesFilter($row, $filter['field'], $filter['value'], $filter['type']),
            ));
        }

        return $rows;
    }

    /** @param array<string, mixed> $row */
    private function rowMatchesFilter(array $row, string $field, string $value, string $type): bool
    {
        return 'select' === $type && isset($row[$field]) && is_scalar($row[$field]) && (string) $row[$field] === $value;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private function applySorting(array $rows): array
    {
        $sort = $this->getCurrentSort();

        if (!$this->isSortableColumn($sort)) {
            return $rows;
        }

        usort($rows, function (array $left, array $right) use ($sort): int {
            $comparison = strnatcasecmp($this->getCellValue($left, $sort), $this->getCellValue($right, $sort));

            return 'desc' === $this->getCurrentDirection() ? -$comparison : $comparison;
        });

        return $rows;
    }

    private function isSortableColumn(string $key): bool
    {
        foreach ($this->getColumns() as $column) {
            if ($column['key'] === $key && $column['sortable']) {
                return true;
            }
        }

        return false;
    }
}