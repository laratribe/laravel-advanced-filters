<?php

use Laratribe\AdvancedFilters\Filters\SetFilter;
use Laratribe\AdvancedFilters\Tests\Fixtures\FilterTestModel;

function statusFilter(): SetFilter
{
    return SetFilter::make('status')->options([
        'active' => 'Active',
        'paused' => 'Paused',
    ])->multiple();
}

it('only allows declared option values', function () {
    expect(statusFilter()->validate('equals', 'active'))->toBe(['value' => 'active'])
        ->and(statusFilter()->validate('equals', 'bogus'))->toBeNull();
});

it('cleans an "in" list against allowed values', function () {
    expect(statusFilter()->validate('in', ['active', 'bogus', 'paused']))
        ->toBe(['value' => ['active', 'paused']]);
});

it('builds a whereIn clause', function () {
    $query = statusFilter()->applyToQuery(FilterTestModel::query(), 'in', ['active', 'paused']);

    expect($query->toSql())->toContain('in (')
        ->and($query->getBindings())->toBe(['active', 'paused']);
});

// The panel preselects the first clause, so multiple() has to reorder — otherwise the
// flag is inert and the user lands on a single-value select.
it('leads with the OR clauses when multiple', function () {
    expect(array_map(fn ($c) => $c->operator(), statusFilter()->getAvailableClauses()))
        ->toBe(['in', 'not_in', 'equals', 'not_equals']);
});

it('leads with equals when not multiple', function () {
    $filter = SetFilter::make('status')->options(['active' => 'Active']);

    expect(array_map(fn ($c) => $c->operator(), $filter->getAvailableClauses()))
        ->toBe(['equals', 'not_equals', 'in', 'not_in']);
});

it('exposes only equals when clause selector is hidden', function () {
    $filter = SetFilter::make('status')->options(['active' => 'Active'])->withoutClause();

    expect(array_map(fn ($c) => $c->operator(), $filter->getAvailableClauses()))->toBe(['equals']);
});

it('serialises options to optionItems for the frontend', function () {
    $definition = collect(FilterTestModel::filterDefinitions())->firstWhere('key', 'status');

    expect($definition)->not->toHaveKey('options')
        ->and($definition['optionItems'])->toContain(['value' => 'active', 'label' => 'Active']);
});
