# Custom panels

Pick the lightest option that covers you.

| You want | Do this |
|---|---|
| Different markup only | `view` prop or `views` config |
| Different markup for one region | `chip` / `trigger` / `applyButton` slots |
| Extra server behaviour, same engine | Extend `Livewire\Panel` |
| Total control | Write your own against the wire contract |

## Your own markup

```blade
<x-advanced-filters::panel :fields="$filterFields" view="filters.my-panel" />
<x-advanced-filters::panel :fields="$filterFields" :inputs="['string', 'number']" />
```

```php
// config/advanced-filters.php — applies everywhere
'views' => ['panel' => 'filters.my-panel', 'livewire_panel' => 'filters.my-livewire-panel'],
```

Or publish and edit the originals:

```bash
php artisan vendor:publish --tag=advanced-filters-views
```

::: tip
Publishing means you own those files forever, including their bugs. Prefer the `view` prop
for wholesale replacement and slots for partial — both survive package updates.
:::

## Extending the Livewire panel

Keeps validation, server-derived field definitions and the `advanced-filters-updated`
dispatch. You add only what's missing:

```php
use Laratribe\AdvancedFilters\Livewire\Panel;

class MyFilterPanel extends Panel
{
    public bool $panelOpen = false;

    public function toggle(): void
    {
        $this->panelOpen = ! $this->panelOpen;
    }

    /** Drop every row for one column — not something the packaged panel does. */
    public function clearField(string $field): void
    {
        $this->active = array_values(array_filter($this->active, fn ($row) => $row['field'] !== $field));

        $this->dispatch('advanced-filters-updated', filters: $this->active);
    }

    public function render()
    {
        return view('filters.my-panel', ['fields' => $this->fields()]);
    }
}
```

`addFilter()`, `removeFilter()`, `clear()` and the `fields()` computed property are
inherited. `addFilter()` still runs rows through `normalizeFilters()`, so your panel can't
accidentally become the weak link in validation.

## Writing one from scratch

A Blade component, a Livewire component, a Vue component, or none of the above. The only
contract is [the wire contract](/guide/wire-contract): render
`Model::filterDefinitions()`, collect `{field, operator, value, valueTo?}` rows, hand
them back through `normalizeFilters()` / `applyFilters()`.

If you want the two-step builder behaviour without the packaged markup, bind your own markup
to the shipped Alpine component:

```blade
<div x-data="advancedFiltersPanel({ fields: @js($fields), active: @js($active), config: {}, prefix: 'af' })">
```

This is everything it exposes:

| | |
|---|---|
| State | `fields`, `active`, `pending`, `open`, `step`, `config`, `prefix` |
| Choosing a column | `selectField(field)`, `selectedField()`, `fieldByKey(key)`, `fieldLabel(key)` |
| Operators | `availableOperators()`, `operatorLabel(op, fieldKey?)`, `onOperatorChange()` |
| Which input to show | `needsValueInput()`, `isBetween()`, `isMultiValue()`, `supportsMultilineOr()`, `shape()`, `optionItems()` |
| Applying | `canAdd()`, `addPending()`, `remove(i)`, `clearAll()` |
| Chips | `chipValue(filter)`, `chipKey(filter, i)`, `resolveLabel(fieldKey, raw)` |
| Menu | `toggleMenu()`, `closeMenu()`, `goBackToFields()` |
| Reloading | `apply()`, `buildUrl()` |

Use `advancedFiltersBuilder({ fields, onAdd })` instead when something else owns the active
rows — that's what the Livewire panel does, forwarding `onAdd` to the server.

For a Vue or React panel, see [Inertia](/frontends/inertia) — the same rules are available as
plain functions.
