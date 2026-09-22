<?php

use Illuminate\Support\Facades\View;
use Laratribe\AdvancedFilters\Facades\AdvancedFilters;
use Laratribe\AdvancedFilters\Livewire\Panel;
use Laratribe\AdvancedFilters\Tests\Fixtures\CustomClauseTestModel;
use Laratribe\AdvancedFilters\Tests\Fixtures\FilterTestModel;
use Livewire\Livewire;

// The Livewire adapter is optional; skip the suite when Livewire is not installed.
beforeEach(function () {
    if (! class_exists(Livewire::class)) {
        $this->markTestSkipped('livewire/livewire is not installed.');
    }
});

it('mounts with the model and re-derives field definitions server-side', function () {
    Livewire::test(Panel::class, ['model' => FilterTestModel::class])
        ->assertCount('active', 0)
        ->assertSet('fields', fn ($fields) => count($fields) === 5);
});

it('validates and appends a filter through the same engine', function () {
    Livewire::test(Panel::class, ['model' => FilterTestModel::class])
        ->call('addFilter', 'score', 'greater_than', '20')
        ->assertCount('active', 1)
        ->assertSet('active.0', ['field' => 'score', 'operator' => 'greater_than', 'value' => 20.0])
        ->assertDispatched('advanced-filters-updated');
});

it('drops an invalid filter row', function () {
    Livewire::test(Panel::class, ['model' => FilterTestModel::class])
        ->call('addFilter', 'score', 'greater_than', 'not-a-number')
        ->assertCount('active', 0);
});

it('removes a filter by index', function () {
    Livewire::test(Panel::class, ['model' => FilterTestModel::class])
        ->call('addFilter', 'name', 'contains', 'foo')
        ->call('addFilter', 'name', 'contains', 'bar')
        ->call('removeFilter', 0)
        ->assertCount('active', 1)
        ->assertSet('active.0.value', 'bar');
});

it('clears all filters', function () {
    Livewire::test(Panel::class, ['model' => FilterTestModel::class])
        ->call('addFilter', 'name', 'contains', 'foo')
        ->call('clear')
        ->assertCount('active', 0);
});

it('renders every registered value input partial', function () {
    AdvancedFilters::register('boolean', 'af-test::boolean');
    View::addNamespace('af-test', __DIR__.'/../Fixtures/views');

    Livewire::test(Panel::class, ['model' => FilterTestModel::class])
        ->assertSee("selectedField().input === 'string'", false)
        ->assertSee("selectedField().input === 'boolean'", false);
});

// Chips are server-rendered here, so the label has to come from clauseItems rather
// than the operator map this view used to keep its own copy of.
it('labels a custom operator on a server-rendered chip', function () {
    Livewire::test(Panel::class, ['model' => CustomClauseTestModel::class])
        ->call('addFilter', 'published_at', 'within_last_days', 7)
        ->assertCount('active', 1)
        ->assertSet('active.0', ['field' => 'published_at', 'operator' => 'within_last_days', 'value' => 7])
        ->assertSee('Within last N days');
});

it('renders panel markup supplied by the host app', function () {
    View::addNamespace('af-test', __DIR__.'/../Fixtures/views');

    Livewire::test(Panel::class, [
        'model' => FilterTestModel::class,
        'view' => 'af-test::livewire-panel',
    ])->assertSee('my-own-livewire-panel');
});
