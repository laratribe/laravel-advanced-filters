# Quick start

Two steps on the server, then pick a frontend.

## 1. Declare filters on your model

```php
use Laratribe\AdvancedFilters\Concerns\HasFilters;
use Laratribe\AdvancedFilters\Contracts\Filterable;
use Laratribe\AdvancedFilters\Filters\{TextFilter, SetFilter, NumericFilter, DateFilter};

class Product extends Model implements Filterable
{
    use HasFilters;

    public static function filters(): array
    {
        return [
            TextFilter::make('name', 'Name'),
            TextFilter::make('sku', 'SKU'),
            SetFilter::make('category', 'Category')->options([
                'electronics' => 'Electronics',
                'books' => 'Books',
            ])->multiple(),
            NumericFilter::make('price', 'Price'),
            DateFilter::make('released_at', 'Released'),
        ];
    }
}
```

This method is the **allow-list**. Anything not declared here cannot be filtered on,
whatever the request says.

## 2. Filter and paginate

The trait gives you three things: `normalizeFilters()` to validate request input, the
`applyFilters()` query scope, and `filterFieldsForFrontend()` for the UI.

```php
public function index(Request $request)
{
    $columnFilters = Product::normalizeFilters($request->input('column_filters'));

    $products = Product::query()
        ->where('active', true)          // your own constraints
        ->applyFilters($columnFilters)    // the trait scope
        ->orderBy('name')
        ->paginate(25)
        ->withQueryString();

    return view('products.index', [
        'products'      => $products,
        'filterFields'  => Product::filterFieldsForFrontend(),
        'activeFilters' => $columnFilters,
    ]);
}
```

`applyFilters()` normalises internally, so passing raw request input works too —
`normalizeFilters()` is only needed separately when you want the cleaned rows back for the
UI. Normalisation is idempotent, so calling it twice is harmless.

::: tip Why pass `activeFilters` back?
They're the *normalised* rows — invalid ones already dropped. Echoing those to the frontend
rather than the raw input keeps the chips honest about what was actually applied.
:::

### Shortcut: `FilterableTable`

For a plain "filter and paginate" page, this folds the three calls into one chain. Requires
the model to `implements Filterable` — the trait already satisfies it.

```php
use Laratribe\AdvancedFilters\Support\FilterableTable;

$table = FilterableTable::for(Product::class)
    ->query(fn ($q) => $q->where('active', true))   // optional custom base query
    ->fromRequest($request);

return view('products.index', [
    'products'      => $table->paginate(25),
    'filterFields'  => $table->fieldDefinitions(),
    'activeFilters' => $table->activeFilters(),
]);
```

Reach for the scope directly whenever you need real control over the query — joins,
`groupBy`, `havingRaw`, totals, exports.

## 3. Pick a frontend

| | |
|---|---|
| [Blade + Alpine](/frontends/blade-alpine) | Zero build step |
| [Livewire](/frontends/livewire) | 3 and 4 |
| [Inertia](/frontends/inertia) | Vue or React, your own component |
| [JSON API](/frontends/json-api) | No UI at all |
