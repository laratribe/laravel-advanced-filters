<?php

namespace Laratribe\AdvancedFilters\Facades;

use Illuminate\Support\Facades\Facade;
use Laratribe\AdvancedFilters\Support\FilterTypeRegistry;

/**
 * Entry point for registering filter types from your own service provider:
 *
 *     AdvancedFilters::register('boolean', 'filters.boolean');
 *
 * @method static FilterTypeRegistry register(string $type, string $view)
 * @method static FilterTypeRegistry registerMany(array<string, string> $types)
 * @method static FilterTypeRegistry forget(string $type)
 * @method static bool has(string $type)
 * @method static string|null viewFor(string $type)
 * @method static list<string> inputViews()
 * @method static list<string> inputViewsFor(array<int, string> $types)
 * @method static array<string, string> all()
 *
 * @see FilterTypeRegistry
 */
class AdvancedFilters extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FilterTypeRegistry::class;
    }
}
