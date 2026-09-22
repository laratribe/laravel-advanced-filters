<?php

use Laratribe\AdvancedFilters\Facades\AdvancedFilters;
use Laratribe\AdvancedFilters\Support\FilterTypeRegistry;

it('ships the four built-in types in render order', function () {
    expect(app(FilterTypeRegistry::class)->all())->toBe([
        'select' => 'advanced-filters::components.inputs.select',
        'number' => 'advanced-filters::components.inputs.number',
        'date' => 'advanced-filters::components.inputs.date',
        'string' => 'advanced-filters::components.inputs.string',
    ]);
});

it('appends a newly registered type', function () {
    AdvancedFilters::register('boolean', 'filters.boolean');

    expect(AdvancedFilters::has('boolean'))->toBeTrue()
        ->and(AdvancedFilters::viewFor('boolean'))->toBe('filters.boolean')
        ->and(AdvancedFilters::inputViews())->toContain('filters.boolean');
});

it('replaces a built-in view without moving it', function () {
    AdvancedFilters::register('date', 'filters.my-date');

    expect(AdvancedFilters::inputViews())->toBe([
        'advanced-filters::components.inputs.select',
        'advanced-filters::components.inputs.number',
        'filters.my-date',
        'advanced-filters::components.inputs.string',
    ]);
});

it('renders a shared view only once', function () {
    AdvancedFilters::register('duration', 'advanced-filters::components.inputs.number');

    $views = AdvancedFilters::inputViews();

    expect(array_count_values($views)['advanced-filters::components.inputs.number'])->toBe(1)
        ->and(AdvancedFilters::has('duration'))->toBeTrue();
});

it('forgets a type', function () {
    AdvancedFilters::forget('date');

    expect(AdvancedFilters::has('date'))->toBeFalse()
        ->and(AdvancedFilters::inputViews())->not->toContain('advanced-filters::components.inputs.date');
});

it('narrows the views to a subset of types, in registration order', function () {
    expect(AdvancedFilters::inputViewsFor(['string', 'select', 'nope']))->toBe([
        'advanced-filters::components.inputs.select',
        'advanced-filters::components.inputs.string',
    ]);
});

it('rejects an empty type or view', function () {
    expect(fn () => AdvancedFilters::register('', 'filters.boolean'))->toThrow(InvalidArgumentException::class)
        ->and(fn () => AdvancedFilters::register('boolean', ''))->toThrow(InvalidArgumentException::class);
});

it('seeds types declared in config', function () {
    config()->set('advanced-filters.types', ['boolean' => 'filters.boolean']);
    app()->forgetInstance(FilterTypeRegistry::class);

    expect(app(FilterTypeRegistry::class)->viewFor('boolean'))->toBe('filters.boolean');
});
