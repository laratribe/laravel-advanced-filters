# JSON API (no UI)

Steps 1 and 2 of the [quick start](/guide/quick-start) have no frontend dependency — no
Blade, no Alpine, no Livewire. For a SPA, a mobile client, or any UI you render yourself,
use the package as a filter engine and talk to it through
[the wire contract](/guide/wire-contract).

Two endpoints are usually enough:

```php
use Laratribe\AdvancedFilters\Support\FilterableTable;

// GET /api/products/filters — the column list, for building your UI
public function filters()
{
    return response()->json(['fields' => Product::filterFieldsForFrontend()]);
}

// GET|POST /api/products — filtered, paginated results
public function index(Request $request)
{
    return response()->json(
        FilterableTable::for(Product::class)->fromRequest($request)->paginate(25)
    );
}
```

## Request format

`fromRequest()` reads the configured `query_key` from wherever `$request->input()` finds it —
**query string or JSON body**. Both of these work identically:

```
GET /api/products?column_filters[0][field]=status&column_filters[0][operator]=in&column_filters[0][value][0]=active
```

```jsonc
// POST /api/products
{ "column_filters": [ { "field": "price", "operator": "greater_than", "value": 20 } ] }
```

Laravel resolves the input source by content type *before* it looks at the verb, so any
method with `Content-Type: application/json` works — including the
[HTTP QUERY method](https://httpwg.org/http-extensions/draft-ietf-httpbis-safe-method-w-body.html)
once your router can dispatch it. Nothing in the package inspects the HTTP method.

## Response

The paginator serialises straight to JSON:

```jsonc
{ "data": [...], "current_page": 1, "total": 42, "per_page": 25, "last_page": 2 }
```

`activeFilters()` returns the normalised rows — echo those into your UI's chips so they
reflect what was actually applied.

## You don't need to validate the input

Every row goes through the same engine the Blade panel uses. A `field` the model doesn't
declare, or an `operator` that field doesn't allow, is **dropped before it reaches SQL**
rather than raising an error. `filters()` is the allow-list, so a client-supplied column name
can never reach the query.

```php
// 'password' is not declared in filters() — silently dropped
['field' => 'password', 'operator' => 'contains', 'value' => 'x']
```

Adding your own validation layer on top is redundant. If you'd rather reject bad input
loudly than ignore it, compare `count($request->input('column_filters'))` against
`count($table->activeFilters())` and 422 on a mismatch.
