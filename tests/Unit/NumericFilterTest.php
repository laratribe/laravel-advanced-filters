<?php

use Laratribe\AdvancedFilters\Filters\NumericFilter;
use Laratribe\AdvancedFilters\Tests\Fixtures\FilterTestModel;

it('builds a WHERE comparison', function () {
    $query = NumericFilter::make('score')->applyToQuery(FilterTestModel::query(), 'greater_than', '10');

    expect($query->toSql())->toContain('>')
        ->and($query->getBindings())->toBe([10.0]);
});

it('builds a BETWEEN with two bindings', function () {
    $query = NumericFilter::make('score')->applyToQuery(FilterTestModel::query(), 'between', '5', '15');

    expect($query->toSql())->toContain('between')
        ->and($query->getBindings())->toBe([5.0, 15.0]);
});

it('uses HAVING with a raw expression when configured', function () {
    $query = NumericFilter::make('clicks')
        ->havingExpression('COALESCE(SUM(m.clicks), 0)')
        ->applyToQuery(FilterTestModel::query(), 'greater_than_or_equal', '100');

    expect($query->toSql())->toContain('having')
        ->and($query->toSql())->toContain('COALESCE(SUM(m.clicks), 0) >=')
        ->and($query->getBindings())->toBe([100.0]);
});

it('builds a HAVING BETWEEN', function () {
    $query = NumericFilter::make('clicks')
        ->havingExpression('SUM(m.clicks)')
        ->applyToQuery(FilterTestModel::query(), 'between', '5', '15');

    expect($query->toSql())->toContain('SUM(m.clicks) BETWEEN ? AND ?')
        ->and($query->getBindings())->toBe([5.0, 15.0]);
});

it('casts validated values to float', function () {
    expect(NumericFilter::make('score')->validate('equals', '12'))->toBe(['value' => 12.0]);
});

it('requires both bounds for between', function () {
    expect(NumericFilter::make('score')->validate('between', '5', null))->toBeNull();
});

it('rejects non-numeric input', function () {
    expect(NumericFilter::make('score')->validate('equals', 'abc'))->toBeNull();
});
