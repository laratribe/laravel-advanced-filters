<?php

namespace Laratribe\AdvancedFilters\Livewire;

use Laratribe\AdvancedFilters\Contracts\Filterable;
use Laratribe\AdvancedFilters\Support\FilterTypeRegistry;
use Livewire\Attributes\Computed;
use Livewire\Component;
use RuntimeException;

/**
 * Livewire adapter for the Advanced Filters panel.
 *
 * The component owns the active filter rows as a public property (serialised to the
 * client) and re-derives field definitions server-side on every render — it never
 * trusts the client for the definitions. The two-step builder UI is Alpine-enhanced
 * (no per-keystroke round trips); only `addFilter` hits the server, where the row is
 * validated through the exact same `normalizeFilters` path as a full request.
 *
 * It dispatches `advanced-filters-updated` with the active rows; a host table component
 * listens and re-queries.
 *
 * @phpstan-type FilterRow array{field: string, operator: string, value?: mixed, valueTo?: mixed}
 */
class Panel extends Component
{
    /** @var class-string<Filterable> */
    public string $model;

    /** @var array<int, FilterRow> */
    public array $active = [];

    /** Render different panel markup than the packaged one. */
    public ?string $view = null;

    public function mount(string $model, array $active = [], ?string $view = null): void
    {
        if (! is_subclass_of($model, Filterable::class)) {
            throw new RuntimeException("[{$model}] must implement ".Filterable::class.' (use the HasFilters trait).');
        }

        $this->model = $model;
        $this->view = $view;
        // Re-validate any inbound rows so the component starts from a clean state.
        $this->active = $model::normalizeFilters($active);
    }

    /**
     * Field definitions, always re-derived server-side (the wire contract OUT).
     *
     * @return list<array<string, mixed>>
     */
    #[Computed]
    public function fields(): array
    {
        return ($this->model)::filterDefinitions();
    }

    public function addFilter(string $field, string $operator, mixed $value = null, mixed $valueTo = null): void
    {
        $row = ['field' => $field, 'operator' => $operator, 'value' => $value, 'valueTo' => $valueTo];

        // Validate through the same engine a controller would use; skip invalid rows.
        $normalized = ($this->model)::normalizeFilters([$row]);
        if ($normalized === []) {
            return;
        }

        $this->active[] = $normalized[0];
        $this->emitUpdated();
    }

    public function removeFilter(int $index): void
    {
        unset($this->active[$index]);
        $this->active = array_values($this->active);
        $this->emitUpdated();
    }

    public function clear(): void
    {
        $this->active = [];
        $this->emitUpdated();
    }

    protected function emitUpdated(): void
    {
        $this->dispatch('advanced-filters-updated', filters: $this->active);
    }

    public function render()
    {
        // Passed as view data rather than a computed property: a Livewire view has no
        // component-method scope, unlike the Blade component's.
        return view(
            $this->view ?? config('advanced-filters.views.livewire_panel', 'advanced-filters::livewire.filter-panel'),
            ['inputViews' => app(FilterTypeRegistry::class)->inputViews()],
        );
    }
}
