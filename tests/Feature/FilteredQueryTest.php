<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Laratribe\AdvancedFilters\Support\FilteredQuery;
use Laratribe\AdvancedFilters\Tests\Fixtures\FilterTestModel;

beforeEach(function () {
    migrateFilterTable();
    seedFilterRecords([
        ['name' => 'Alice', 'status' => 'active', 'score' => 10, 'published_at' => '2026-01-10'],
        ['name' => 'Bob', 'status' => 'paused', 'score' => 25, 'published_at' => '2026-03-15'],
        ['name' => 'Carol', 'status' => 'archived', 'score' => 40, 'published_at' => '2026-06-20'],
    ]);
});

it('reads, normalises and applies filters from a request', function () {
    $request = Request::create('/records', 'GET', [
        'column_filters' => [
            ['field' => 'score', 'operator' => 'greater_than', 'value' => '20'],
        ],
    ]);

    $table = FilteredQuery::for(FilterTestModel::class)->fromRequest($request);

    expect($table->get()->pluck('name')->all())->toEqualCanonicalizing(['Bob', 'Carol'])
        ->and($table->activeFilters())->toBe([
            ['field' => 'score', 'operator' => 'greater_than', 'value' => 20.0],
        ]);
});

it('accepts a custom base query', function () {
    $table = FilteredQuery::for(FilterTestModel::class)
        ->query(fn ($q) => $q->where('status', '!=', 'archived'))
        ->withFilters([
            ['field' => 'score', 'operator' => 'greater_than', 'value' => '5'],
        ]);

    expect($table->get()->pluck('name')->all())->toEqualCanonicalizing(['Alice', 'Bob']);
});

it('paginates with the query string appended', function () {
    $table = FilteredQuery::for(FilterTestModel::class)->withFilters([]);

    expect($table->paginate(2)->total())->toBe(3);
});

it('exposes field definitions', function () {
    $table = FilteredQuery::for(FilterTestModel::class);

    expect($table->fieldDefinitions())->toHaveCount(5);
});

it('rejects a model that is not Filterable', function () {
    FilteredQuery::for(Model::class);
})->throws(InvalidArgumentException::class);
