# Installation

```bash
composer require laratribe/laravel-advanced-filters
```

The service provider auto-registers. **The PHP side works immediately** — no publishing
required. If you want the shipped UI, also set up
[the frontend assets](/frontends/blade-alpine#assets-and-styling) for your stack.

## Requirements

| | |
|---|---|
| PHP | 8.2+ |
| Laravel | 11 or 12 |
| Livewire *(optional)* | 3 or 4 — only for the Livewire panel |
| Alpine.js *(optional)* | only for the Blade panel |

## Publishable tags

Publish only what you actually need — none of these are required to use the package.

```bash
php artisan vendor:publish --tag=advanced-filters-config    # config/advanced-filters.php
php artisan vendor:publish --tag=advanced-filters-assets    # public/ JS + CSS (zero-build apps)
php artisan vendor:publish --tag=advanced-filters-js        # resources/js Alpine controller (Vite)
php artisan vendor:publish --tag=advanced-filters-css       # resources/css vanilla stylesheet
php artisan vendor:publish --tag=advanced-filters-views     # override the base Blade markup
php artisan vendor:publish --tag=advanced-filters-theme     # opt-in Tailwind-styled views
```

::: tip Publishing views overrides; registering adds
Published views take precedence automatically — Laravel checks
`resources/views/vendor/advanced-filters` before the package's own. Delete a published file
to fall back to the package version for just that file.

To *add* a new input view rather than replace one, use
[the type registry](/extending/filter-types) instead. No publishing needed.
:::
