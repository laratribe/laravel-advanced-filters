<?php

use Illuminate\Database\Eloquent\Builder;
use Laratribe\AdvancedFilters\Contracts\ClauseContract;
use Laratribe\AdvancedFilters\Filters\Clause;
use Laratribe\AdvancedFilters\Filters\CustomClause;
use Laratribe\AdvancedFilters\Filters\TextFilter;
use Laratribe\AdvancedFilters\Tests\Fixtures\FilterTestModel;

it('derives a readable label from the operator', function () {
    expect(CustomClause::make('within_last_days')->label())->toBe('Within Last Days')
        ->and(CustomClause::make('within_last_days', 'Within last N days')->label())->toBe('Within last N days');
});

it('defaults to a single value and can declare another shape', function () {
    expect(CustomClause::make('matches_regex')->valueShape())->toBe(ClauseContract::SHAPE_SINGLE)
        ->and(CustomClause::make('is_true')->withoutValue()->valueShape())->toBe(ClauseContract::SHAPE_NONE)
        ->and(CustomClause::make('spans')->withRange()->valueShape())->toBe(ClauseContract::SHAPE_RANGE)
        ->and(CustomClause::make('any_tag')->withMultiple()->valueShape())->toBe(ClauseContract::SHAPE_MULTI);
});

it('keeps earlier instances untouched when chaining', function () {
    $base = CustomClause::make('is_true');
    $shaped = $base->withoutValue();

    expect($base->valueShape())->toBe(ClauseContract::SHAPE_SINGLE)
        ->and($shaped->valueShape())->toBe(ClauseContract::SHAPE_NONE)
        ->and($shaped)->not->toBe($base);
});

/**
 * Clauses are matched by operator string, never by object identity — two separately
 * constructed instances of the same operator have to behave as one clause.
 */
it('matches a separately constructed instance of the same operator', function () {
    $filter = TextFilter::make('name')->clauses([CustomClause::make('sounds_like')]);

    expect($filter->supportsClause('sounds_like'))->toBeTrue();
});

it('applies and validates through its own closures', function () {
    $filter = TextFilter::make('name')->clauses([
        CustomClause::make('sounds_like', 'Sounds like')
            ->apply(fn (Builder $q, string $column, $value) => $q->where($column, 'like', $value.'%'))
            ->validate(fn ($v) => $v === 'bad' ? null : ['value' => strtoupper((string) $v)]),
    ]);

    expect($filter->validate('sounds_like', 'foo'))->toBe(['value' => 'FOO'])
        ->and($filter->validate('sounds_like', 'bad'))->toBeNull();

    $query = $filter->applyToQuery(FilterTestModel::query(), 'sounds_like', 'foo');

    expect($query->getBindings())->toBe(['foo%']);
});

it('is a harmless no-op when no apply closure is supplied', function () {
    $filter = TextFilter::make('name')->clauses([Clause::Equals, CustomClause::make('sounds_like')]);

    $query = $filter->applyToQuery(FilterTestModel::query(), 'sounds_like', 'foo');

    expect($query->getBindings())->toBe([]);
});
