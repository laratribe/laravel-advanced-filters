<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use Laratribe\AdvancedFilters\Facades\AdvancedFilters;
use Livewire\Livewire;
use Workbench\App\Livewire\ProductResults;

class WorkbenchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Persistent sqlite file so the migrated demo data survives across requests.
        $database = __DIR__.'/../../database/database.sqlite';
        if (! file_exists($database)) {
            @touch($database);
        }

        config([
            'database.default' => 'workbench',
            'database.connections.workbench' => [
                'driver' => 'sqlite',
                'database' => $database,
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'workbench');

        // The app-defined "boolean" filter type. Both panels pick this up on their
        // next render — no package view is published or forked.
        AdvancedFilters::register('boolean', 'workbench::filters.boolean');

        if (class_exists(Livewire::class)) {
            Livewire::component('product-results', ProductResults::class);
        }
    }
}
