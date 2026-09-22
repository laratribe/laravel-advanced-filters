<?php

namespace Workbench\App\Livewire;

use Laratribe\AdvancedFilters\Support\FilterableTable;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Workbench\App\Models\Product;

/**
 * Demo of the event-driven integration: this table listens for the package
 * Panel's `advanced-filters-updated` event and re-queries server-side.
 */
class ProductResults extends Component
{
    use WithPagination;

    /** @var array<int, array<string, mixed>> */
    public array $filters = [];

    #[On('advanced-filters-updated')]
    public function updateFilters(array $filters): void
    {
        $this->filters = $filters;
        $this->resetPage();
    }

    public function render()
    {
        $products = FilterableTable::for(Product::class)
            ->withFilters($this->filters)
            ->paginate(15);

        return view('workbench::livewire.product-results', ['products' => $products]);
    }
}
