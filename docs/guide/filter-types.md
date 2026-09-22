# Filter types

| Type | Class | Default operators | Notable options |
|------|-------|-------------------|-----------------|
| Text | `TextFilter` | contains, not_contains, starts_with, ends_with, equals, not_equals | `maxLength()`, multi-line input → OR |
| Number | `NumericFilter` | equals, not_equals, >, <, ≥, ≤, between | `havingExpression()` for aggregates |
| Date | `DateFilter` | equals, >, <, between | strict `Y-m-d` |
| Set | `SetFilter` | equals, not_equals, in, not_in | `options()`, `pluckOptionsFromModel()`, `multiple()`, `withoutClause()` |

Need something else? See [custom filter types](/extending/filter-types).

## Shared options

Every filter extends `BaseFilter` and gets these:

```php
TextFilter::make('name', 'Campaign name')
    ->column('campaigns.name')        // map the key to a different column
    ->clauses([Clause::Equals])       // restrict the operator list
    ->nullable()                      // adds is_empty / is_not_empty
    ->input('sku-picker')             // render a different value input
    ->applyUsing(fn (Builder $q, string $column, ClauseContract $clause, $value) => /* … */)
    ->validateUsing(fn ($value) => /* normalised value, or null to drop the row */);
```

## Text

Multi-line input becomes an OR across the lines, but only for *positive* operators —
"does not contain A or B" would read as the opposite of what it does.

```php
TextFilter::make('sku')->maxLength(64);
```

LIKE wildcards in user input are escaped, so a value of `50%` searches for a literal `50%`.

## Numeric

For aggregate columns, `havingExpression()` switches the filter from `WHERE` to `HAVING`:

```php
NumericFilter::make('clicks', 'Clicks')->havingExpression('COALESCE(SUM(m.clicks), 0)');
```

::: warning
`havingExpression()` takes raw SQL. It is **developer-supplied**, never user input — the
*value* is always bound as a parameter, but the expression itself is interpolated.
:::

## Date

Validation is strict `Y-m-d`; anything else drops the row. `equals` uses `whereDate()` so a
datetime column matches the whole day.

## Set

```php
SetFilter::make('status', 'Status')
    ->options(['active' => 'Active', 'paused' => 'Paused'])
    ->multiple();
```

Values are whitelisted against `options()` on both validation and apply, so a client can
never filter by a value you didn't declare.

`multiple()` promotes `in` / `not_in` to the front of the operator list. Since the panel
preselects the first operator, picking that column lands the user straight on a
multi-select — an OR match. `equals` stays available in the dropdown.

`pluckOptionsFromModel()` fills the options from a table:

```php
SetFilter::make('account_id', 'Account')->pluckOptionsFromModel(Account::class, 'name');
```

::: tip
This runs a query each time the filter set is built. For hot paths, cache the array and
pass it to `options()` yourself.
:::
