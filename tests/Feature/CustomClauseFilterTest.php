<?php

use Laratribe\AdvancedFilters\Filters\DateFilter;
use Laratribe\AdvancedFilters\Tests\Fixtures\CustomClauseTestModel;
use Laratribe\AdvancedFilters\Tests\Fixtures\WithinDaysDateFilter;

beforeEach(function () {
    migrateFilterTable();

    CustomClauseTestModel::insert([
        ['name' => 'Recent', 'status' => 'active', 'score' => 1, 'published_at' => now()->subDays(2)->toDateString()],
        ['name' => 'Older', 'status' => 'active', 'score' => 2, 'published_at' => now()->subDays(40)->toDateString()],
    ]);
});

/**
 * The regression this suite exists for: a filter that only overrides applyCustomClause()
 * must survive normalize(). If validateCustomClause() defaulted to null the row would be
 * dropped before it ever reached the query — silently, and only on the applyFilters()
 * path, while a direct applyToQuery() call kept working.
 */
it('keeps a custom operator through normalisation', function () {
    $rows = CustomClauseTestModel::normalizeFilters([
        ['field' => 'published_at', 'operator' => 'within_last_days', 'value' => 7],
    ]);

    expect($rows)->toBe([
        ['field' => 'published_at', 'operator' => 'within_last_days', 'value' => 7],
    ]);
});

it('applies a custom operator through the applyFilters scope', function () {
    $names = CustomClauseTestModel::query()
        ->applyFilters([['field' => 'published_at', 'operator' => 'within_last_days', 'value' => 7]])
        ->pluck('name')->all();

    expect($names)->toBe(['Recent']);
});

it('runs the inline apply/validate closures of a CustomClause', function () {
    $names = CustomClauseTestModel::query()
        ->applyFilters([['field' => 'name', 'operator' => 'sounds_like', 'value' => 'Rec']])
        ->pluck('name')->all();

    expect($names)->toBe(['Recent'])
        ->and(CustomClauseTestModel::normalizeFilters([
            ['field' => 'name', 'operator' => 'sounds_like', 'value' => ''],
        ]))->toBe([]);
});

/**
 * The BC guarantee: adding an operator must not disturb the built-ins, which still
 * route through the untouched defaultApply().
 */
it('leaves the built-in clauses of the filter it extends alone', function () {
    $filter = WithinDaysDateFilter::make('published_at');

    $custom = $filter->applyToQuery(CustomClauseTestModel::query(), 'between', '2024-01-01', '2024-12-31');
    $stock = DateFilter::make('published_at')
        ->applyToQuery(CustomClauseTestModel::query(), 'between', '2024-01-01', '2024-12-31');

    expect($custom->toSql())->toBe($stock->toSql())
        ->and($custom->getBindings())->toBe($stock->getBindings());
});

it('still rejects operators the filter does not declare', function () {
    expect(CustomClauseTestModel::normalizeFilters([
        ['field' => 'published_at', 'operator' => 'matches_regex', 'value' => 'x'],
    ]))->toBe([]);
});

it('exposes the custom operator in the field definitions', function () {
    $fields = collect(CustomClauseTestModel::filterFieldsForFrontend())->keyBy('key');

    expect($fields['published_at']['clauses'])->toContain('within_last_days')
        ->and($fields['published_at']['clauseItems'])->toContain([
            'value' => 'within_last_days',
            'label' => 'Within last N days',
            'shape' => 'single',
        ]);
});
