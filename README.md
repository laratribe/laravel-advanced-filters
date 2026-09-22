# Laravel Advanced Filters

[![tests](https://github.com/laratribe/laravel-advanced-filters/actions/workflows/tests.yml/badge.svg)](https://github.com/laratribe/laravel-advanced-filters/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/laratribe/laravel-advanced-filters.svg)](https://packagist.org/packages/laratribe/laravel-advanced-filters)
[![License](https://img.shields.io/packagist/l/laratribe/laravel-advanced-filters.svg)](LICENSE.md)

Declare filters once on an Eloquent model, then render them with **Blade + Alpine**,
**Livewire**, **Inertia (Vue/React)**, or no UI at all as a **JSON API**.

📖 **[Full documentation](https://laratribe.github.io/laravel-advanced-filters)**

- 🔌 **One trait** — add `HasFilters` and a `filters()` method to a model. That's the setup.
- 🪞 **Self-describing** — the server tells the frontend which columns exist, which operators each allows, their labels and how many values they take. That's what makes a generic panel possible.
- 🎛️ **Four frontends, one backend** — no duplicated filter logic between them.
- 🧩 **Extensible** — add your own filter types (with their own input views) and your own operators, without forking a shipped view.
- 🎨 **CSS-framework-agnostic** — semantic markup, stable `af-*` classes, a bundled vanilla theme, and an opt-in Tailwind one.
- 🔒 **Safe by construction** — `filters()` is the allow-list. Undeclared columns and disallowed operators are dropped before they reach SQL.

## Installation

```bash
composer require laratribe/laravel-advanced-filters
```

The service provider auto-registers and the PHP side works immediately. For the shipped UI,
see [frontend assets](https://laratribe.github.io/laravel-advanced-filters/frontends/blade-alpine#assets-and-styling).

Requires PHP 8.2+ and Laravel 11 or 12. Livewire 3/4 and Alpine are optional.

## Quick start

**1. Declare filters on the model**

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

**2. Filter and paginate**

```php
$filters = Product::normalizeFilters($request->input('column_filters'));

$products = Product::query()
    ->where('active', true)        // your own constraints
    ->applyFilters($filters)        // the trait scope
    ->paginate(25)
    ->withQueryString();

return view('products.index', [
    'products'      => $products,
    'filterFields'  => Product::filterFieldsForFrontend(),
    'activeFilters' => $filters,
]);
```

**3. Render it**

```blade
<x-advanced-filters::panel
    :fields="$filterFields"
    :active="$activeFilters"
    :base-url="route('products.index')"
/>
```

That's a working filter UI. See the docs for
[Livewire](https://laratribe.github.io/laravel-advanced-filters/frontends/livewire),
[Inertia](https://laratribe.github.io/laravel-advanced-filters/frontends/inertia) and
[JSON API](https://laratribe.github.io/laravel-advanced-filters/frontends/json-api).

## Filter types

| Type | Class | Default operators |
|------|-------|-------------------|
| Text | `TextFilter` | contains, not_contains, starts_with, ends_with, equals, not_equals |
| Number | `NumericFilter` | equals, not_equals, >, <, ≥, ≤, between |
| Date | `DateFilter` | equals, >, <, between |
| Set | `SetFilter` | equals, not_equals, in, not_in |

Plus [your own](https://laratribe.github.io/laravel-advanced-filters/extending/filter-types),
and [your own operators](https://laratribe.github.io/laravel-advanced-filters/extending/operators).

## The wire contract

Two plain-array shapes, no framework coupling, SemVer-stable:

- **Out** — `[{ key, label, type, input, clauses, clauseItems, optionItems? }]`
- **In** — `[{ field, operator, value, valueTo? }]`

[Details](https://laratribe.github.io/laravel-advanced-filters/guide/wire-contract).

## Playground

A Testbench app demonstrating every frontend against a seeded SQLite model, including a
custom filter type and a custom operator:

```bash
composer install
vendor/bin/testbench workbench:build
vendor/bin/testbench serve
# / → Blade + Alpine    /livewire → Livewire
```

## Testing

```bash
composer test     # Pest
composer lint     # Pint
composer analyse  # PHPStan
```

## Contributing & security

Issues and pull requests welcome. `NumericFilter::havingExpression()` accepts raw SQL and is
**developer-supplied, never user input** — values are always bound as parameters. If you find
a security issue, please email ram@weblog-media.com rather than opening a public issue.

## License

MIT. See [LICENSE.md](LICENSE.md).
