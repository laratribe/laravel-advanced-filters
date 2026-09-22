# Livewire

Works on **Livewire 3 and 4**. The adapter registers itself only when Livewire is installed.

```blade
<livewire:advanced-filters::panel :model="\App\Models\Product::class" />
```

The component owns the active rows and dispatches `advanced-filters-updated`. A host table
component listens and re-queries:

```php
use Livewire\Attributes\On;
use Laratribe\AdvancedFilters\Support\FilteredQuery;

#[On('advanced-filters-updated')]
public function updateFilters(array $filters): void
{
    $this->filters = $filters;
    $this->resetPage();
}

public function render()
{
    $products = FilteredQuery::for(Product::class)->withFilters($this->filters)->paginate(25);

    return view('products.results', compact('products'));
}
```

::: warning Don't load a second Alpine
Livewire bundles Alpine. Loading another copy from a CDN breaks both. Import only the
package controller and register it on `livewire:init`, which fires before Livewire starts
Alpine:

```js
import { advancedFiltersPanel, advancedFiltersBuilder } from '.../advanced-filters'

document.addEventListener('livewire:init', () => {
    window.Alpine.data('advancedFiltersPanel', advancedFiltersPanel)
    window.Alpine.data('advancedFiltersBuilder', advancedFiltersBuilder)
})
```
:::

## Why it's Alpine-enhanced

The two-step builder runs client-side so there's no round trip per keystroke. Only
`addFilter` hits the server — where the row goes through the **same** `normalizeFilters()`
path as a full request. Field definitions are re-derived server-side on every render and
never trusted from the client.

## Mount arguments

| | |
|---|---|
| `model` | Required. A class implementing `Filterable` |
| `active` | Pre-applied rows; re-validated on mount |
| `view` | Render your own markup instead of the packaged panel |

## Livewire 4 note

v4's component finder treats any name containing `::` as `namespace::component` and resolves
it *only* through registered namespaces — it never consults components registered the v3 way.
The provider detects the version and registers accordingly, so `advanced-filters::panel`
works on both. Nothing for you to do; worth knowing if you write your own `::`-named
components.

Need extra server-side behaviour? [Extend the Livewire panel](/extending/panel#extending-the-livewire-panel).
