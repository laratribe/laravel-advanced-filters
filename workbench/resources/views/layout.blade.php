<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Advanced Filters — Playground</title>

    <link rel="stylesheet" href="{{ url('/assets/advanced-filters.css') }}">
    @livewireStyles
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; background: #f8fafc; color: #1f2937; }
        .wrap { max-width: 980px; margin: 0 auto; padding: 2rem 1.5rem; }
        h1 { font-size: 1.25rem; }
        nav a { margin-right: 1rem; color: #2563eb; text-decoration: none; }
        table { width: 100%; border-collapse: collapse; background: #fff; margin-top: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.08); border-radius: 8px; overflow: hidden; }
        th, td { text-align: left; padding: 0.5rem 0.75rem; border-bottom: 1px solid #eef2f7; font-size: 0.875rem; }
        th { background: #f1f5f9; font-weight: 600; }
        .card { background: #fff; border-radius: 8px; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        .pagination { margin-top: 1rem; display: flex; gap: .25rem; flex-wrap: wrap; }
        .pagination a, .pagination span { padding: .25rem .5rem; border: 1px solid #e2e8f0; border-radius: 4px; text-decoration: none; color: #2563eb; font-size: .8125rem; }
    </style>

    {{-- Alpine + the package controller (registered before Alpine starts). --}}
    <script type="module">
        import Alpine from 'https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/module.esm.js'
        import { advancedFiltersPanel, advancedFiltersBuilder, registerFilterType } from '{{ url('/assets/advanced-filters.js') }}'

        // The frontend half of the custom "boolean" type declared in WorkbenchServiceProvider.
        // Only the parts that differ from the shape-driven defaults need defining.
        registerFilterType('boolean', {
            emptyValue: () => '1',
            chipValue: filter => (String(filter.value) === '1' ? 'Yes' : 'No'),
        })

        window.Alpine = Alpine
        Alpine.data('advancedFiltersPanel', advancedFiltersPanel)
        Alpine.data('advancedFiltersBuilder', advancedFiltersBuilder)
        Alpine.start()
    </script>
</head>
<body>
    <div class="wrap">
        <h1>Advanced Filters — Playground</h1>
        <nav style="margin-bottom:1.5rem">
            <a href="{{ route('products.index') }}">Blade + Alpine</a>
            <a href="{{ route('products.livewire') }}">Livewire</a>
        </nav>

        @yield('content')
    </div>

    @livewireScripts
</body>
</html>
