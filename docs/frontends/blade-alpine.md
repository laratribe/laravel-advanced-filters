# Blade + Alpine

The default frontend. No build step required.

:::tip See it running
[advanced-filters.laratribe.com/blade](https://advanced-filters.laratribe.com/blade) —
expand *“The code behind this page”* for the model, route and view behind it.
:::

```blade
<x-advanced-filters::panel
    :fields="$filterFields"
    :active="$activeFilters"
    :base-url="route('products.index')"
/>
```

Applying or removing a filter reloads the table — a full-page visit by default, see
[`mode`](/reference/configuration).

## Props

| Prop | Default | Purpose |
|---|---|---|
| `fields` | — | Field definitions from `filterDefinitions()` |
| `active` | `[]` | Normalised active rows |
| `base-url` | current URL | Where applying a filter navigates |
| `mode` | config | `navigate` or `fetch` |
| `target` | — | CSS selector to swap, for `mode="fetch"` |
| `add-label` / `clear-label` | `Add filter` / `Clear all` | Button text |
| `inputs` | all registered | Restrict which value inputs render |
| `view` | config | Render your own panel markup |

## Slots

Replace one region without forking the whole panel:

```blade
<x-advanced-filters::panel :fields="$filterFields">
    <x-slot:trigger>
        <button type="button" class="btn btn-primary" @click="toggleMenu()">Filters</button>
    </x-slot:trigger>
</x-advanced-filters::panel>
```

Available: `trigger`, `chip`, `applyButton`. For more, see [custom panels](/extending/panel).

## Assets and styling

Two things are needed on the page: the **Alpine controller** and some **styling**.

::: warning On a Livewire page, skip the Alpine step
Livewire bundles its own Alpine. Loading a second one from a CDN breaks both. See
[Livewire](/frontends/livewire).
:::

### The Alpine controller

**Zero build / CDN:**

```bash
php artisan vendor:publish --tag=advanced-filters-assets
```

```html
<script src="{{ asset('vendor/advanced-filters/advanced-filters.js') }}?v={{ filemtime(public_path('vendor/advanced-filters/advanced-filters.js')) }}" type="module"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
```

::: danger Version the URL
These are plain published files, not Vite-built. Browsers cache ES modules aggressively, so
without a cache-buster you keep running the *previous* bundle after
`vendor:publish --force` — which shows up as value inputs that silently fail to render.
`filemtime()` is enough.
:::

**Vite:**

```bash
php artisan vendor:publish --tag=advanced-filters-js
```

```js
import Alpine from 'alpinejs'
import { advancedFiltersPanel, advancedFiltersBuilder } from './vendor/advanced-filters'
Alpine.data('advancedFiltersPanel', advancedFiltersPanel)
Alpine.data('advancedFiltersBuilder', advancedFiltersBuilder)
Alpine.start()
```

The module self-registers on `alpine:init` if `window.Alpine` exists, so a bare
`import './vendor/advanced-filters'` also works — **as long as it is evaluated before Alpine
starts.**

### Styling — choose one

Default views are unstyled structure with stable `af-*` classes and `data-af-part` hooks.

**Vanilla CSS** — any stack, no Tailwind:

```bash
php artisan vendor:publish --tag=advanced-filters-css
```

Recolour via CSS custom properties on `.af-panel`: `--af-accent`, `--af-border`, `--af-bg`,
`--af-fg`, `--af-radius`, and others.

**Tailwind v4** — publish the pre-themed views:

```bash
php artisan vendor:publish --tag=advanced-filters-theme
```

v4 auto-detects sources under your project, so published views are scanned automatically.
Reading them straight from `vendor/` instead needs:

```css
@source "../../vendor/laratribe/laravel-advanced-filters/resources/views";
```

**Tailwind v3** — publish the theme, then add to `content`:

```js
content: ['./resources/views/vendor/advanced-filters/**/*.blade.php', /* … */]
```

**Your own design system** — skip the bundled CSS. Restyle the `af-*` classes, or pass your
framework's classes through the attribute bag:

```blade
<x-advanced-filters::panel :fields="$filterFields" class="card p-3" />
```

::: tip
`class_prefix` is configurable, but the bundled stylesheet hardcodes `.af-*`. Change the
prefix only if you're writing your own CSS.
:::
