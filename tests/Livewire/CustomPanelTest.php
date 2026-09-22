<?php

use Illuminate\Support\Facades\View;
use Laratribe\AdvancedFilters\Tests\Fixtures\CustomPanel;
use Laratribe\AdvancedFilters\Tests\Fixtures\FilterTestModel;
use Livewire\Livewire;

// The Livewire adapter is optional; skip the suite when Livewire is not installed.
beforeEach(function () {
    if (! class_exists(Livewire::class)) {
        $this->markTestSkipped('livewire/livewire is not installed.');
    }

    View::addNamespace('af-test', __DIR__.'/../Fixtures/views');
});

/**
 * Proves the "bring your own panel" path: a host app subclasses the packaged Livewire
 * component, replaces the markup entirely, and still inherits the filter engine.
 */
it('renders host-owned markup instead of the packaged panel', function () {
    Livewire::test(CustomPanel::class, ['model' => FilterTestModel::class])
        ->assertSee('my-custom-livewire-panel', false)
        ->assertDontSee('data-af-part="builder"', false);
});

it('inherits validation from the packaged panel', function () {
    Livewire::test(CustomPanel::class, ['model' => FilterTestModel::class])
        ->call('addFilter', 'score', 'greater_than', '20')
        ->assertSet('active.0', ['field' => 'score', 'operator' => 'greater_than', 'value' => 20.0])
        ->assertDispatched('advanced-filters-updated')
        ->call('addFilter', 'score', 'greater_than', 'not-a-number')
        ->assertCount('active', 1);
});

it('still derives field definitions server-side', function () {
    Livewire::test(CustomPanel::class, ['model' => FilterTestModel::class])
        ->call('toggle')
        ->assertSee('Status')
        ->assertSee('(select)');
});

it('supports behaviour the packaged panel does not have', function () {
    Livewire::test(CustomPanel::class, ['model' => FilterTestModel::class])
        ->call('addFilter', 'name', 'contains', 'foo')
        ->call('addFilter', 'name', 'contains', 'bar')
        ->call('addFilter', 'score', 'greater_than', '5')
        ->assertCount('active', 3)
        ->call('clearField', 'name')
        ->assertCount('active', 1)
        ->assertSet('active.0.field', 'score')
        ->assertDispatched('advanced-filters-updated');
});
