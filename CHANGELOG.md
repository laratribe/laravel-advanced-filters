# Changelog

All notable changes to `laratribe/laravel-advanced-filters` are documented here.
This project follows [Semantic Versioning](https://semver.org). The public API is the
wire contract (field definitions / filter rows), the `Clause` enum string values, and the
`HasFilters` / `BaseFilter` / `FilteredQuery` method signatures.

## [1.0.0] - 2026-09-22

First release.

### The engine

- `HasFilters` trait — declare filters with a `filters()` method on any Eloquent model, and
  get `normalizeFilters()`, the `applyFilters()` query scope, and `filterFieldsForFrontend()`.
- Four filter types: `TextFilter` (multi-line input → OR), `NumericFilter` (`WHERE` or
  `HAVING` for aggregates via `havingExpression()`), `DateFilter` (strict `Y-m-d`), and
  `SetFilter` (`options()`, `pluckOptionsFromModel()`, `multiple()`, `withoutClause()`).
- `FilterSet` — the shared engine, usable standalone against raw query builders with no model.
- `FilteredQuery` — optional controller sugar folding normalise → apply → paginate into one
  chain. Named for what it produces: it builds a filtered query, and knows nothing about
  tables, columns or rendering.
- `filters()` is an allow-list: an undeclared field, a disallowed operator, or a value that
  fails validation is dropped before it reaches SQL rather than raising an error.

### Frontends

One backend definition drives all four; none of them reimplement the filter rules.

- **Blade + Alpine** — `<x-advanced-filters::panel />`, zero build step, with `chip`,
  `trigger` and `applyButton` slots.
- **Livewire 3 and 4** — `<livewire:advanced-filters::panel />`, event-driven via
  `advanced-filters-updated`. Field definitions are re-derived server-side every render and
  never trusted from the client.
- **Inertia / SPA** — the builder rules ship as framework-free functions (`clauseShape()`,
  `canAddRow()`, `coerceRow()`, `emptyValueFor()`, `buildFilterUrl()`), so a Vue composable or
  React hook is thin glue. `config.onApply` hands the panel's URL to your router instead of
  navigating.
- **JSON API** — no UI at all. `fromRequest()` reads the filter rows from a query string or a
  JSON body, so any HTTP method works.

### Extension points

- **Custom operators** — `ClauseContract` (`operator()`, `label()`, `valueShape()`) opens the
  operator set. Define them inline with `CustomClause::make()->apply()->validate()`, or on
  your own backed enum for reuse. `applyCustomClause()` / `validateCustomClause()` are called
  only for non-built-in operators, so the shipped ones can't be broken.
- **Custom filter types** — register an input view with `AdvancedFilters::register()` or the
  `types` config key, and all three panels render it. No package view is published or forked.
- **`BaseFilter::input()`** — render one field with a different input view while keeping its
  type, and therefore its validation, coercion and chip rendering.
- **JS type handlers** — `registerFilterType()` supplies `emptyValue`, `canAdd`, `coerce`,
  `chipValue` or `needsValueInput` for a custom type. Also on `window.AdvancedFilters` for the
  zero-build path.
- **Custom panels** — a `view` prop, a `views` config key, slots, or extend
  `Livewire\Panel` to add server behaviour while inheriting the engine.

### Styling

- Semantic markup with stable `af-*` classes and `data-af-part` hooks.
- Bundled vanilla stylesheet, themed with CSS custom properties — no Tailwind required.
- Opt-in Tailwind theme (v3 and v4), publishable, with slot and `class_prefix` parity.

### Requirements

PHP 8.2+ and Laravel 10, 11, 12 or 13. Livewire 3/4 and Alpine are optional. Verified
against Laravel 10.50, 11.56, 12.69 and 13.32.

### Quality

- 107 Pest tests covering the engine, every filter type, the Blade panel, the Livewire
  adapter, custom operators, custom types and the JSON API path.
- Pint and PHPStan (Larastan, level 5) clean.
- Testbench workbench demonstrating every frontend, plus a custom type and custom operator.
