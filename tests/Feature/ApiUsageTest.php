<?php

use Illuminate\Http\Request;
use Laratribe\AdvancedFilters\Support\FilterableTable;
use Laratribe\AdvancedFilters\Tests\Fixtures\FilterTestModel;

/**
 * Headless usage: the package as a JSON API, with no Blade, Alpine or Livewire involved.
 * The wire contract is the whole integration surface, so these tests pin the two shapes a
 * JS/mobile client depends on — definitions out, filter rows in.
 */
beforeEach(function () {
    migrateFilterTable();

    seedFilterRecords([
        ['name' => 'Alice', 'email' => 'a@example.test', 'status' => 'active', 'score' => 10, 'published_at' => '2024-01-01'],
        ['name' => 'Bob', 'email' => null, 'status' => 'paused', 'score' => 30, 'published_at' => '2024-06-01'],
        ['name' => 'Carol', 'email' => 'c@example.test', 'status' => 'active', 'score' => 50, 'published_at' => '2024-12-01'],
    ]);
});

it('serves field definitions as JSON', function () {
    $definitions = FilterTestModel::filterFieldsForFrontend();

    // Must survive a JSON round trip unchanged — this is what a JS client consumes.
    $decoded = json_decode((string) json_encode($definitions), true);

    expect($decoded)->toBe($definitions)
        ->and($decoded[0])->toHaveKeys(['key', 'label', 'type', 'input', 'clauses', 'clauseItems']);
});

it('reads filter rows from a JSON request body', function () {
    $request = Request::create('/api/records', 'POST', server: ['CONTENT_TYPE' => 'application/json'], content: (string) json_encode([
        'column_filters' => [
            ['field' => 'status', 'operator' => 'equals', 'value' => 'active'],
            ['field' => 'score', 'operator' => 'greater_than', 'value' => 20],
        ],
    ]));

    $table = FilterableTable::for(FilterTestModel::class)->fromRequest($request);

    expect($table->get()->pluck('name')->all())->toBe(['Carol'])
        ->and($table->activeFilters())->toBe([
            ['field' => 'status', 'operator' => 'equals', 'value' => 'active'],
            ['field' => 'score', 'operator' => 'greater_than', 'value' => 20.0],
        ]);
});

it('paginates into a JSON-serialisable payload', function () {
    $request = Request::create('/api/records', 'GET', ['column_filters' => [
        ['field' => 'status', 'operator' => 'equals', 'value' => 'active'],
    ]]);

    $payload = json_decode((string) json_encode(
        FilterableTable::for(FilterTestModel::class)->fromRequest($request)->paginate(1)
    ), true);

    expect($payload)->toHaveKeys(['data', 'current_page', 'total', 'per_page'])
        ->and($payload['total'])->toBe(2)
        ->and($payload['data'])->toHaveCount(1);
});

it('rejects a column the model does not expose', function () {
    $request = Request::create('/api/records', 'GET', ['column_filters' => [
        ['field' => 'email', 'operator' => 'contains', 'value' => 'example'],
        ['field' => 'password', 'operator' => 'contains', 'value' => 'x'],
    ]]);

    $table = FilterableTable::for(FilterTestModel::class)->fromRequest($request);

    // The unknown column is dropped rather than erroring, and never reaches SQL.
    expect($table->activeFilters())->toHaveCount(1)
        ->and($table->activeFilters()[0]['field'])->toBe('email')
        ->and($table->builder()->toSql())->not->toContain('password');
});

it('rejects an operator the field does not allow', function () {
    $request = Request::create('/api/records', 'GET', ['column_filters' => [
        ['field' => 'score', 'operator' => 'contains', 'value' => '1'],
    ]]);

    expect(FilterableTable::for(FilterTestModel::class)->fromRequest($request)->activeFilters())->toBe([]);
});
