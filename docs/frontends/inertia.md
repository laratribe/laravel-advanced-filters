# Inertia (Vue / React)

The server code is identical to [the quick start](/guide/quick-start) — swap `view()` for
`Inertia::render()` and pass the same props:

```php
return Inertia::render('Products/Index', [
    'products'      => $query->paginate(25),
    'filterFields'  => $query->filterDefinitions(),
    'activeFilters' => $query->activeFilters(),
]);
```

The package ships no Vue or React component. It ships **the rules**, as framework-free
functions, so your component is markup and reactivity rather than a reimplementation.

## The helpers

```js
import {
    clauseShape, canAddRow, coerceRow, emptyValueFor,
    resolveOptionLabel, buildFilterUrl, filterTypeHandler,
} from '.../advanced-filters'
```

| Function | Answers |
|---|---|
| `clauseShape(field, operator)` | `none` / `single` / `range` / `multi` — which input to render |
| `canAddRow(pending, field)` | Is the row complete? |
| `coerceRow(pending, field)` | The row to send, numbers cast |
| `emptyValueFor(operator, field)` | Initial value when the operator changes |
| `resolveOptionLabel(field, raw)` | A select value's display label |
| `buildFilterUrl(rows, config)` | The query string carrying the wire contract |

None of them touch the DOM or import a framework, so they run in Node and under test.

## Driving the router

`buildFilterUrl()` builds the URL; your router performs the visit:

```js
import { router } from '@inertiajs/vue3'

function applyFilters(rows) {
    router.get(buildFilterUrl(rows, { baseUrl }), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['products', 'activeFilters'],   // skip re-sending field definitions
    })
}
```

It rewrites only its own `column_filters` keys and drops `page`, leaving everything else
alone — so sorting, per-page and any other query state survive, and clearing all filters
doesn't wipe them:

```
/products?sort=-price&columns=name,price&column_filters[0][field]=category&…
```

## Keeping chips honest

The server echoes back *normalised* rows, so sync from the prop rather than trusting local
state:

```js
const rows = ref([...props.activeFilters])
watch(() => props.activeFilters, next => { rows.value = [...next] })
```

## The Alpine panel takes the same route

Pass `onApply` in the panel config and the shipped Blade panel hands you the URL instead of
navigating — so an Inertia page can reuse the packaged UI:

```js
config.onApply = (url, rows) => router.get(url, {}, { preserveState: true })
```

It's a plain callback, not an Inertia import, so it works for Turbo or any custom router too.

## A worked example

The package's development repo contains a full Inertia + Vue implementation against real
data — a Vuetify panel plus a `useAdvancedFilters` composable. The composable is ~170 lines
of pure Vue reactivity and the component ~230 lines of markup, with **zero** filter logic in
either: every rule comes from the helpers above.

That's the measure of whether you're using this right. If your component contains
`operator === 'between'`, reach for `clauseShape()` instead.
