# Custom operators

Operators are `ClauseContract` implementations — three methods:

```php
interface ClauseContract
{
    public function operator(): string;    // 'within_last_days' — the wire identifier
    public function label(): string;       // 'Within last N days' — shown in the dropdown
    public function valueShape(): string;  // SHAPE_NONE | SHAPE_SINGLE | SHAPE_RANGE | SHAPE_MULTI
}
```

The built-in `Clause` enum is one implementation. Yours sit alongside it in the same
`clauses()` list — there's no separate registry.

::: tip Why `valueShape()` matters
It's what makes a custom operator *usable*, not just labelled. Without it the frontend
would render a text input for `is_true`, and the Add button could never satisfy its
validation. The shape tells every frontend whether to render no input, one, two, or a
multi-select.
:::

## Inline, for one filter

No subclass needed — `CustomClause` carries its own query and validation logic:

```php
use Laratribe\AdvancedFilters\Filters\{Clause, CustomClause, DateFilter};

DateFilter::make('released_at', 'Released')->clauses([
    Clause::Between,
    CustomClause::make('within_last_days', 'Within last N days')
        ->apply(fn (Builder $q, string $column, $value) => $q->where($column, '>=', now()->subDays((int) $value)))
        ->validate(fn ($value) => is_numeric($value) && $value > 0 ? ['value' => (int) $value] : null),
]);
```

Shape helpers: `->withoutValue()`, `->withRange()`, `->withMultiple()`, or `->shape(...)`.
The class is `readonly`, so each setter returns a new instance.

## Reusable, as your own enum

For an operator used across several filters, implement the contract on a backed enum and get
`match()` and IDE completion:

```php
enum DateClause: string implements ClauseContract
{
    case WithinLastDays = 'within_last_days';

    public function operator(): string { return $this->value; }
    public function label(): string { return 'Within last N days'; }
    public function valueShape(): string { return self::SHAPE_SINGLE; }
}
```

Then handle it in a `BaseFilter` subclass:

```php
class WithinDaysDateFilter extends DateFilter
{
    protected function defaultClauses(): array
    {
        return [...parent::defaultClauses(), DateClause::WithinLastDays];
    }

    protected function applyCustomClause(Builder $query, ClauseContract $clause, mixed $value, mixed $valueTo): Builder
    {
        return $query->where($this->getColumn(), '>=', now()->subDays((int) $value));
    }
}
```

## The two hooks

`applyCustomClause()` and `validateCustomClause()` are called **only** for operators outside
the `Clause` enum. So you handle just your own operators, you can't break the built-ins, and
you never need `parent::`.

`validateCustomClause()` defaults to **passing the value through**, which means
`applyCustomClause()` alone is enough to ship an operator. Override it when you need to
reject or coerce input.

::: warning Why pass-through and not null
Returning `null` from validation means *drop this row*. If the default were `null`, a filter
that only overrode `applyCustomClause()` would work via a direct `applyToQuery()` call but
silently do nothing through `applyFilters()` — no error, just no results. Pass-through avoids
that trap.
:::

## Safety

Custom operators are validated exactly like built-ins. An operator a filter doesn't declare
is dropped in `normalize()` and never reaches the query. Clause matching compares operator
*strings*, not object identity, so two separately constructed `CustomClause` instances for
the same operator behave as one.
