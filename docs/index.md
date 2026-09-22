---
layout: home

hero:
  name: Laravel Advanced Filters
  text: One definition, any frontend
  tagline: Declare filters on an Eloquent model. Render them with Blade + Alpine, Livewire, Inertia, or nothing at all.
  actions:
    - theme: brand
      text: Get started
      link: /guide/installation
    - theme: alt
      text: Quick start
      link: /guide/quick-start
    - theme: alt
      text: View on GitHub
      link: https://github.com/laratribe/laravel-advanced-filters

features:
  - title: Self-describing
    details: The server tells the frontend which columns exist, which operators each allows, their labels and how many values they take. That is what makes a generic panel possible — the UI never hardcodes your schema.
  - title: Four frontends, one backend
    details: Blade + Alpine with zero build step, Livewire 3 and 4, Inertia with Vue or React, or no UI at all as a JSON API for a SPA or mobile client.
  - title: Extensible where it counts
    details: Add your own filter types with their own input views, and your own operators carrying their own labels and value shapes. No forking a shipped view.
  - title: Safe by construction
    details: The filters() method is the allow-list. A column you did not declare, or an operator a field does not allow, is dropped before it reaches SQL.
---

## In thirty seconds

```php
class Product extends Model implements Filterable
{
    use HasFilters;

    public static function filters(): array
    {
        return [
            TextFilter::make('name', 'Name'),
            SetFilter::make('category', 'Category')->options([...])->multiple(),
            NumericFilter::make('price', 'Price'),
        ];
    }
}
```

```php
$products = Product::query()
    ->applyFilters($request->input('column_filters'))
    ->paginate(25);
```

```blade
<x-advanced-filters::panel :fields="Product::filterFieldsForFrontend()" :active="$activeFilters" />
```

That's a working filter UI. Everything else in these docs is about changing how it looks,
adding types and operators, or dropping the UI entirely.
