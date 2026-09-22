{{-- A host app's own panel markup, pointed at via the `view` prop. --}}
<div class="my-own-panel" x-data="advancedFiltersPanel(@js(['fields' => $fields, 'active' => $active, 'config' => $config(), 'prefix' => $prefix()]))"></div>
