# Configuration

```bash
php artisan vendor:publish --tag=advanced-filters-config
```

```php
// config/advanced-filters.php
return [
    'query_key'    => 'column_filters',
    'mode'         => 'navigate',
    'class_prefix' => 'af',
    'types'        => [],
    'views'        => [
        'panel'          => 'advanced-filters::components.panel',
        'livewire_panel' => 'advanced-filters::livewire.filter-panel',
    ],
];
```

## `query_key`

The request key filter rows are read from and written to. Change it if it collides with
something in your app — the frontend picks it up automatically, since the panel receives it
in its config blob.

## `mode`

How the Blade panel applies a filter.

| | |
|---|---|
| `navigate` | Full-page visit to the base URL with filters in the query string. Works everywhere, no JS infrastructure |
| `fetch` | Requests the URL and swaps a target element, with `history.pushState`. Needs a `target` selector on the panel |

Both are overridable per panel via the `mode` prop. For an SPA, use
[`config.onApply`](/frontends/inertia#the-alpine-panel-takes-the-same-route) instead of either.

## `class_prefix`

Prefix for every CSS class in the shipped markup (`af-panel`, `af-chip`, …).

::: warning
The bundled stylesheet hardcodes `.af-*`. Change this only if you're writing your own CSS —
otherwise the styles stop matching.
:::

## `types`

Maps a filter type to the Blade view that renders its value input. Added to the four
built-ins; reusing a built-in name replaces its view without moving its position.

```php
'types' => ['boolean' => 'filters.boolean'],
```

Equivalent to `AdvancedFilters::register()` from a service provider — use whichever fits.
See [custom filter types](/extending/filter-types).

## `views`

Point the panels at your own markup globally, without publishing. Also overridable per
instance with the `view` prop. See [custom panels](/extending/panel).

## Testing the package itself

```bash
composer test     # Pest
composer lint     # Pint
composer analyse  # PHPStan / Larastan
```

The workbench is a Testbench app demonstrating every frontend against a seeded SQLite model,
including a custom filter type and a custom operator:

```bash
vendor/bin/testbench workbench:build
vendor/bin/testbench serve
# / → Blade + Alpine    /livewire → Livewire
```
