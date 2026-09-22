<?php

use Laratribe\AdvancedFilters\Filters\TextFilter;
use Laratribe\AdvancedFilters\Tests\Fixtures\FilterTestModel;

it('builds a LIKE clause for contains', function () {
    $query = TextFilter::make('name')->applyToQuery(FilterTestModel::query(), 'contains', 'foo');

    expect($query->toSql())->toContain('like')
        ->and($query->getBindings())->toBe(['%foo%']);
});

it('escapes LIKE wildcards in the value', function () {
    $query = TextFilter::make('name')->applyToQuery(FilterTestModel::query(), 'contains', 'a%b_c');

    expect($query->getBindings())->toBe(['%a\%b\_c%']);
});

it('uses exact comparison for equals', function () {
    $query = TextFilter::make('name')->applyToQuery(FilterTestModel::query(), 'equals', 'foo');

    expect($query->toSql())->toContain('=')
        ->and($query->getBindings())->toBe(['foo']);
});

it('validates and trims a single value', function () {
    expect(TextFilter::make('name')->validate('contains', '  foo  '))->toBe(['value' => 'foo']);
});

it('rejects empty values', function () {
    expect(TextFilter::make('name')->validate('contains', '   '))->toBeNull();
});

it('splits multi-line input into an OR array for positive clauses', function () {
    $result = TextFilter::make('name')->validate('contains', "foo\nbar\nfoo");

    expect($result)->toBe(['value' => ['foo', 'bar']]); // de-duplicated
});

it('does not split multi-line input for negative clauses', function () {
    $result = TextFilter::make('name')->validate('not_contains', "foo\nbar");

    expect($result['value'])->toBeString();
});

it('truncates to maxLength', function () {
    $result = TextFilter::make('name')->maxLength(3)->validate('contains', 'abcdef');

    expect($result)->toBe(['value' => 'abc']);
});

it('returns an empty payload for is_empty without a value', function () {
    expect(TextFilter::make('name')->nullable()->validate('is_empty', null))->toBe([]);
});

it('wraps multiple OR values in a grouped where', function () {
    $query = TextFilter::make('name')->applyToQuery(FilterTestModel::query(), 'contains', ['foo', 'bar']);

    expect($query->getBindings())->toBe(['%foo%', '%bar%']);
});

it('validates an array value (idempotent re-normalisation of multi-line OR)', function () {
    // The value an earlier normalise produced for multi-line input.
    $result = TextFilter::make('name')->validate('contains', ['foo', 'bar', 'foo']);

    expect($result)->toBe(['value' => ['foo', 'bar']]); // de-duplicated, still an array
});

it('collapses an array value to the first entry for negative clauses', function () {
    $result = TextFilter::make('name')->validate('not_contains', ['foo', 'bar']);

    expect($result)->toBe(['value' => 'foo']);
});
