# Custom filter types

Three pieces, all in your app. No package view is published or forked.

## 1. The filter class

Subclass `BaseFilter` and return a new `type()`:

```php
use Laratribe\AdvancedFilters\Filters\{BaseFilter, Clause};

class BooleanFilter extends BaseFilter
{
    public function type(): string { return 'boolean'; }

    protected function defaultClauses(): array { return [Clause::Equals]; }

    protected function defaultApply(Builder $query, Clause $clause, mixed $value, mixed $valueTo): Builder
    {
        return $query->where($this->getColumn(), '=', (int) $value);
    }

    protected function defaultValidate(string $operator, mixed $value, mixed $valueTo): ?array
    {
        return in_array((string) $value, ['0', '1'], true) ? ['value' => (string) $value] : null;
    }
}
```

`BaseFilter::__construct` is `final`, so add configuration through fluent setters rather than
constructor arguments — that's what keeps `make()` working on subclasses.

## 2. The value input

A Blade partial that self-gates on the field's `input` name, exactly like the shipped ones.
`$prefix` is passed in:

```blade
{{-- resources/views/filters/boolean.blade.php --}}
<template x-if="selectedField().input === 'boolean'">
    <div class="{{ $prefix }}-input-group" data-af-part="input">
        <select class="{{ $prefix }}-select" x-model="pending.value">
            <option value="1">Yes</option>
            <option value="0">No</option>
        </select>
    </div>
</template>
```

Every registered input renders into the DOM; Alpine shows the one whose guard matches. Keep
a single root element inside the `<template>`.

## 3. Register it

From a service provider:

```php
use Laratribe\AdvancedFilters\Facades\AdvancedFilters;

public function boot(): void
{
    AdvancedFilters::register('boolean', 'filters.boolean');
}
```

Or in config:

```php
'types' => ['boolean' => 'filters.boolean'],
```

**All three panels** — headless, Tailwind and Livewire — render it from that point on,
because they iterate the registry rather than hardcoding a list of includes.

## Optionally, a JS handler

The frontend derives behaviour from the operator's [value shape](/guide/wire-contract#field-definitions-out),
which covers most types. Supply only what differs:

```js
import { registerFilterType } from '.../advanced-filters'

registerFilterType('boolean', {
    emptyValue: () => '1',                                               // initial pending value
    chipValue: filter => (String(filter.value) === '1' ? 'Yes' : 'No'),  // chip text
    // also: canAdd(pending, field), coerce(pending, field), needsValueInput(operator, field)
})
```

Without a bundler, the same helpers are on `window.AdvancedFilters`.

## A new field view

To render one field differently *without* inventing a type, point it at another registered
input. The filter keeps its `type`, and therefore its validation, coercion and chip
rendering:

```php
TextFilter::make('sku', 'SKU')->input('sku-picker');   // type stays "string"
```

That's the difference between the two keys in the payload: `type` is behaviour, `input` is
appearance.

## Registry API

```php
AdvancedFilters::register('boolean', 'filters.boolean');   // add or replace
AdvancedFilters::registerMany(['boolean' => 'filters.boolean']);
AdvancedFilters::forget('date');                            // drop a built-in
AdvancedFilters::has('boolean');
AdvancedFilters::viewFor('boolean');
AdvancedFilters::inputViews();                              // ordered, deduped
AdvancedFilters::all();
```

Two types may share one view — it renders once. Registration order is DOM order.
