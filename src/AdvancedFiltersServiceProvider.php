<?php

namespace Laratribe\AdvancedFilters;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Laratribe\AdvancedFilters\Support\FilterTypeRegistry;
use Livewire\LivewireManager;

class AdvancedFiltersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/advanced-filters.php', 'advanced-filters');

        $this->registerTypeRegistry();
    }

    /**
     * The built-ins are registered under the `advanced-filters::` view namespace, so
     * `vendor:publish --tag=advanced-filters-views` still overrides their markup.
     * Types declared in config are applied last and can therefore replace them.
     */
    protected function registerTypeRegistry(): void
    {
        $this->app->singleton(FilterTypeRegistry::class, fn ($app) => (new FilterTypeRegistry)
            ->register('select', 'advanced-filters::components.inputs.select')
            ->register('number', 'advanced-filters::components.inputs.number')
            ->register('date', 'advanced-filters::components.inputs.date')
            ->register('string', 'advanced-filters::components.inputs.string')
            ->registerMany($app['config']->get('advanced-filters.types', [])));
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'advanced-filters');

        // <x-advanced-filters::panel />, <x-advanced-filters::chips />, ...
        Blade::componentNamespace('Laratribe\\AdvancedFilters\\View\\Components', 'advanced-filters');

        $this->registerLivewire();

        if ($this->app->runningInConsole()) {
            $this->registerPublishing();
        }
    }

    /**
     * Register the Livewire adapter only when Livewire is installed — it is an optional dependency.
     *
     * v3 and v4 need different registrations. In v4 the component finder treats any name
     * containing "::" as `namespace::component` and resolves it *only* through registered
     * namespaces — it returns early without ever consulting the explicitly registered
     * components — so v3's Livewire::component('advanced-filters::panel', ...) silently
     * resolves to nothing. addNamespace() exists only in v4, which makes it the version
     * discriminator as well as the fix.
     */
    protected function registerLivewire(): void
    {
        if (! class_exists(LivewireManager::class)) {
            return;
        }

        // Resolved rather than called through the facade so the version check applies to
        // the actual bound manager — addNamespace() does not exist on the v3 class.
        $livewire = $this->app->make(LivewireManager::class);

        if (method_exists($livewire, 'addNamespace')) {
            // Livewire 4: `advanced-filters::panel` → Laratribe\AdvancedFilters\Livewire\Panel
            $livewire->addNamespace('advanced-filters', classNamespace: __NAMESPACE__.'\\Livewire');

            return;
        }

        // Livewire 3
        $livewire->component('advanced-filters::panel', Livewire\Panel::class);
    }

    protected function registerPublishing(): void
    {
        $this->publishes([
            __DIR__.'/../config/advanced-filters.php' => config_path('advanced-filters.php'),
        ], 'advanced-filters-config');

        // Override the headless Blade markup.
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/advanced-filters'),
        ], 'advanced-filters-views');

        // The vanilla, framework-neutral default stylesheet (to customise).
        $this->publishes([
            __DIR__.'/../resources/css/advanced-filters.css' => resource_path('css/vendor/advanced-filters.css'),
        ], 'advanced-filters-css');

        // The Alpine controller, for Vite-based apps that want to import it.
        $this->publishes([
            __DIR__.'/../resources/js/advanced-filters.js' => resource_path('js/vendor/advanced-filters.js'),
        ], 'advanced-filters-js');

        // Compiled-free assets for non-Vite apps: drop into public/ and <link>/<script> directly.
        $this->publishes([
            __DIR__.'/../resources/css/advanced-filters.css' => public_path('vendor/advanced-filters/advanced-filters.css'),
            __DIR__.'/../resources/js/advanced-filters.js' => public_path('vendor/advanced-filters/advanced-filters.js'),
        ], 'advanced-filters-assets');

        // Opt-in Tailwind theme: pre-styled view overrides (Tailwind utility classes).
        // After publishing, point your Tailwind build at resources/views/vendor/advanced-filters
        // (auto-detected on Tailwind v4; add it to `content` on v3) — see the README.
        $this->publishes([
            __DIR__.'/../resources/views/themes/tailwind' => resource_path('views/vendor/advanced-filters/components'),
        ], 'advanced-filters-theme');
    }
}
