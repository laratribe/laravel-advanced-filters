# The wire contract

The backend and any frontend communicate through two stable JSON shapes. This contract —
plus the `Clause` enum string values and the trait / `BaseFilter` method signatures — is the
package's public API and follows SemVer.

## Field definitions (out)

From `Model::filterFieldsForFrontend()` or `FilterableTable::fieldDefinitions()`:

```jsonc
[
  {
    "key": "status",
    "label": "Status",
    "type": "select",          // the filter class's type()
    "input": "select",         // which value-input view renders it
    "clauses": ["in", "not_in", "equals", "not_equals"],
    "clauseItems": [
      { "value": "in",     "label": "Is any of", "shape": "multi" },
      { "value": "equals", "label": "Equals",    "shape": "single" }
    ],
    "optionItems": [           // SetFilter only
      { "value": "active", "label": "Active" }
    ]
  }
]
```

**`input`** names the value-input view and defaults to `type`. Changing it lets one field
render differently without changing its behaviour — see [custom filter types](/extending/filter-types#a-new-field-view).

**`clauseItems`** is what makes a generic UI possible. Each operator carries its own display
label and its **value shape**:

| Shape | Means | Example |
|---|---|---|
| `none` | no value input at all | `is_empty` |
| `single` | one value | `contains` |
| `range` | two — `value` and `valueTo` | `between` |
| `multi` | a list | `in` |

`Clause::label()` and `Clause::valueShape()` are the source of truth. The JS keeps a
fallback copy of the built-in labels only for payloads that predate `clauseItems`.

## Filter rows (in)

Sent as the configured `query_key` (`column_filters` by default):

```jsonc
[
  { "field": "status", "operator": "in", "value": ["active", "paused"] },
  { "field": "price",  "operator": "between", "value": 10, "valueTo": 50 }
]
```

Rows are ANDed together. As a query string:

```
?column_filters[0][field]=status&column_filters[0][operator]=in&column_filters[0][value][0]=active
```

Because `$request->input()` reads either source, a JSON request body works identically —
which is what the [JSON API path](/frontends/json-api) relies on.

## Validation is not your job

Every row goes through the same engine regardless of frontend:

1. Is `field` declared in `filters()`? If not, **drop the row.**
2. Does that filter allow `operator`? If not, **drop the row.**
3. Does the value validate for that filter type? If not, **drop the row.**

Invalid rows are dropped silently rather than raising an error — the right default for a
filter UI, where a half-built row shouldn't 500 the page. Nothing reaches SQL that
`filters()` didn't authorise.

## Both shapes are plain arrays

No framework coupling in either direction. That's what allows a Vue component, a React
hook, a mobile client and the shipped Blade panel to all drive the same backend.
