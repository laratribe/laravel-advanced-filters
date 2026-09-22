<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default query-string key
    |--------------------------------------------------------------------------
    |
    | The request key the filter rows are read from / written to. A controller
    | reads `$request->input(config('advanced-filters.query_key'))` and the
    | frontend serialises active filters under the same key.
    |
    */
    'query_key' => 'column_filters',

    /*
    |--------------------------------------------------------------------------
    | Default reload mode for the Blade/Alpine panel
    |--------------------------------------------------------------------------
    |
    | "navigate" performs a full-page visit to the base URL with the filters in
    | the query string (works everywhere, zero JS infra). "fetch" requests the
    | URL and swaps a target element via history.pushState. Per-panel override
    | is available through the <x-advanced-filters::panel mode="..." /> prop.
    |
    */
    'mode' => 'navigate',

    /*
    |--------------------------------------------------------------------------
    | Default CSS class prefix
    |--------------------------------------------------------------------------
    |
    | All shipped markup uses this prefix (e.g. "af-panel", "af-chip"). Change
    | it only if it collides with your app; the bundled vanilla CSS targets the
    | default "af" prefix.
    |
    */
    'class_prefix' => 'af',

    /*
    |--------------------------------------------------------------------------
    | Custom filter types
    |--------------------------------------------------------------------------
    |
    | Maps a filter type (the `input` key of the wire contract) to the Blade view
    | that renders its value input. Anything declared here is added to the four
    | built-ins — `select`, `number`, `date` and `string` — and may replace one by
    | reusing its name. Equivalent to calling AdvancedFilters::register() from a
    | service provider; use whichever fits your app.
    |
    |   'types' => ['boolean' => 'filters.boolean'],
    |
    */
    'types' => [],

    /*
    |--------------------------------------------------------------------------
    | View overrides
    |--------------------------------------------------------------------------
    |
    | Point the panels at your own markup without publishing the package views.
    | Both can also be overridden per instance — <x-advanced-filters::panel
    | view="..." /> and <livewire:advanced-filters::panel :view="..." />.
    |
    */
    'views' => [
        'panel' => 'advanced-filters::components.panel',
        'livewire_panel' => 'advanced-filters::livewire.filter-panel',
    ],

];
