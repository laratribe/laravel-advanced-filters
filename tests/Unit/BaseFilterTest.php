<?php

use Illuminate\Database\Eloquent\Builder;
use Laratribe\AdvancedFilters\Contracts\ClauseContract;
use Laratribe\AdvancedFilters\Filters\Clause;
use Laratribe\AdvancedFilters\Filters\CustomClause;
use Laratribe\AdvancedFilters\Filters\TextFilter;
use Laratribe\AdvancedFilters\Tests\Fixtures\FilterTestModel;

it('maps a filter key to a different column', function () {
    $query = TextFilter::make('name')->column('contacts.full_name')
        ->applyToQuery(FilterTestModel::query(), 'equals', 'foo');

    expect($query->toSql())->toContain('contacts');
});

it('adds empty clauses when nullable', function () {
    $clauses = array_map(fn (ClauseContract $c) => $c->operator(), TextFilter::make('email')->nullable()->getAvailableClauses());

    expect($clauses)->toContain('is_empty')->toContain('is_not_empty');
});

it('does not duplicate an empty clause the caller already supplied', function () {
    $clauses = array_map(
        fn (ClauseContract $c) => $c->operator(),
        TextFilter::make('email')
            ->clauses([Clause::Equals, CustomClause::make('is_empty', 'Blank')])
            ->nullable()
            ->getAvailableClauses()
    );

    expect(array_count_values($clauses)['is_empty'])->toBe(1)
        ->and($clauses)->toContain('is_not_empty');
});

it('honours a custom clause list', function () {
    $filter = TextFilter::make('name')->clauses([Clause::Equals]);

    expect($filter->supportsClause('equals'))->toBeTrue()
        ->and($filter->supportsClause('contains'))->toBeFalse();
});

it('ignores operators that are not available', function () {
    $query = TextFilter::make('name')->clauses([Clause::Equals])
        ->applyToQuery(FilterTestModel::query(), 'contains', 'foo');

    expect($query->getBindings())->toBe([]);
});

it('wraps a custom apply callback in a where group by default', function () {
    $query = TextFilter::make('name')
        ->applyUsing(fn (Builder $q, string $col, Clause $clause, mixed $value) => $q->where('name', $value))
        ->applyToQuery(FilterTestModel::query(), 'equals', 'foo');

    expect($query->toSql())->toContain('("name" = ?)');
});

it('does not wrap when applied unwrapped', function () {
    $query = TextFilter::make('name')
        ->applyUsing(fn (Builder $q, string $col, Clause $clause, mixed $value) => $q->where('name', $value), unwrapped: true)
        ->applyToQuery(FilterTestModel::query(), 'equals', 'foo');

    expect($query->toSql())->not->toContain('("name" = ?)')
        ->and($query->toSql())->toContain('"name" = ?');
});

it('uses a custom validation callback', function () {
    $filter = TextFilter::make('name')->validateUsing(fn ($value) => $value === 'ok' ? 'normalised' : null);

    expect($filter->validate('equals', 'ok'))->toBe(['value' => 'normalised'])
        ->and($filter->validate('equals', 'no'))->toBeNull();
});

it('hands a custom clause to the apply callback', function () {
    $seen = null;

    TextFilter::make('name')
        ->clauses([CustomClause::make('sounds_like')])
        ->applyUsing(function (Builder $q, string $col, ClauseContract $clause, mixed $value) use (&$seen) {
            $seen = $clause->operator();

            return $q;
        })
        ->applyToQuery(FilterTestModel::query(), 'sounds_like', 'foo');

    expect($seen)->toBe('sounds_like');
});

it('publishes clauses and their labels in the wire contract', function () {
    $definition = TextFilter::make('name')
        ->clauses([Clause::Equals, CustomClause::make('sounds_like', 'Sounds like')])
        ->toArray();

    expect($definition['clauses'])->toBe(['equals', 'sounds_like'])
        ->and($definition['clauseItems'])->toBe([
            ['value' => 'equals', 'label' => 'Equals', 'shape' => ClauseContract::SHAPE_SINGLE],
            ['value' => 'sounds_like', 'label' => 'Sounds like', 'shape' => ClauseContract::SHAPE_SINGLE],
        ]);
});

it('defaults the input view to the filter type and allows an override', function () {
    expect(TextFilter::make('name')->toArray()['input'])->toBe('string')
        ->and(TextFilter::make('sku')->input('sku-picker')->toArray())
        ->toMatchArray(['type' => 'string', 'input' => 'sku-picker']);
});
