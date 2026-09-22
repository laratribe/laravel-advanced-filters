<?php

namespace Laratribe\AdvancedFilters\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Laratribe\AdvancedFilters\Support\FilterTypeRegistry;

/**
 * The orchestrator component: renders the "Add filter" builder + active chips and
 * bootstraps the Alpine controller with the field definitions and active rows.
 *
 *     <x-advanced-filters::panel
 *         :fields="$filterFields"
 *         :active="$activeFilters"
 *         :base-url="route('campaigns.index')" />
 */
class Panel extends Component
{
    /**
     * @param  list<array<string, mixed>>  $fields  Field definitions (wire contract OUT)
     * @param  array<int, array<string, mixed>>  $active  Active normalised filter rows
     * @param  list<string>|null  $inputs  Restrict the rendered value inputs to these types
     * @param  string|null  $view  Render different panel markup than the packaged one
     */
    public function __construct(
        public array $fields = [],
        public array $active = [],
        public ?string $baseUrl = null,
        public ?string $mode = null,
        public ?string $queryKey = null,
        public ?string $target = null,
        public string $addLabel = 'Add filter',
        public string $clearLabel = 'Clear all',
        public ?array $inputs = null,
        public ?string $view = null,
    ) {}

    /**
     * The config blob handed to the Alpine controller.
     *
     * @return array<string, mixed>
     */
    public function config(): array
    {
        return [
            'baseUrl' => $this->baseUrl,
            'mode' => $this->mode ?? config('advanced-filters.mode', 'navigate'),
            'queryKey' => $this->queryKey ?? config('advanced-filters.query_key', 'column_filters'),
            'target' => $this->target,
        ];
    }

    public function prefix(): string
    {
        return (string) config('advanced-filters.class_prefix', 'af');
    }

    /**
     * The value-input views to render, from the filter type registry — so a custom
     * filter type shows up here without this panel being forked.
     *
     * @return list<string>
     */
    public function inputViews(): array
    {
        $registry = app(FilterTypeRegistry::class);

        return $this->inputs === null
            ? $registry->inputViews()
            : $registry->inputViewsFor($this->inputs);
    }

    public function render(): View
    {
        return view($this->view ?? config('advanced-filters.views.panel', 'advanced-filters::components.panel'));
    }
}
