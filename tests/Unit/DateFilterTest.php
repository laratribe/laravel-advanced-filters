<?php

use Laratribe\AdvancedFilters\Filters\DateFilter;
use Laratribe\AdvancedFilters\Tests\Fixtures\FilterTestModel;

it('uses a date comparison for equals', function () {
    $query = DateFilter::make('published_at')->applyToQuery(FilterTestModel::query(), 'equals', '2026-05-31');

    // whereDate() compiles to a driver-specific date function (strftime on sqlite).
    expect($query->toSql())->toContain('published_at')
        ->and($query->getBindings())->toBe(['2026-05-31']);
});

it('builds a between range', function () {
    $query = DateFilter::make('published_at')->applyToQuery(FilterTestModel::query(), 'between', '2026-01-01', '2026-12-31');

    expect($query->toSql())->toContain('between')
        ->and($query->getBindings())->toBe(['2026-01-01', '2026-12-31']);
});

it('accepts a valid ISO date', function () {
    expect(DateFilter::make('published_at')->validate('equals', '2026-05-31'))->toBe(['value' => '2026-05-31']);
});

it('rejects malformed dates', function () {
    expect(DateFilter::make('published_at')->validate('equals', '31-05-2026'))->toBeNull()
        ->and(DateFilter::make('published_at')->validate('equals', 'not-a-date'))->toBeNull();
});

it('requires both ends for a between range', function () {
    expect(DateFilter::make('published_at')->validate('between', '2026-01-01', null))->toBeNull();
});
