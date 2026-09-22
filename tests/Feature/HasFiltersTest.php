<?php

use Laratribe\AdvancedFilters\Tests\Fixtures\FilterTestModel;

beforeEach(function () {
    migrateFilterTable();
    seedFilterRecords([
        ['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active', 'score' => 10, 'published_at' => '2026-01-10'],
        ['name' => 'Bob', 'email' => null, 'status' => 'paused', 'score' => 25, 'published_at' => '2026-03-15'],
        ['name' => 'Carol', 'email' => 'carol@example.com', 'status' => 'archived', 'score' => 40, 'published_at' => '2026-06-20'],
    ]);
});

it('normalises and drops invalid rows', function () {
    $rows = FilterTestModel::normalizeFilters([
        ['field' => 'name', 'operator' => 'contains', 'value' => 'Al'],     // ok
        ['field' => 'unknown', 'operator' => 'contains', 'value' => 'x'],   // unknown field
        ['field' => 'name', 'operator' => 'bogus', 'value' => 'x'],         // unsupported operator
        ['field' => 'score', 'operator' => 'equals', 'value' => 'abc'],     // invalid value
    ]);

    expect($rows)->toBe([
        ['field' => 'name', 'operator' => 'contains', 'value' => 'Al'],
    ]);
});

it('applies filters to a real query', function () {
    $rows = FilterTestModel::normalizeFilters([
        ['field' => 'score', 'operator' => 'greater_than', 'value' => 20],
    ]);

    $names = FilterTestModel::query()->applyFilters($rows)->pluck('name')->all();

    expect($names)->toEqualCanonicalizing(['Bob', 'Carol']);
});

it('accepts raw (un-normalised) request input directly in applyFilters', function () {
    // No normalizeFilters() call — raw value is a string "20", not a float.
    $names = FilterTestModel::query()
        ->applyFilters([['field' => 'score', 'operator' => 'greater_than', 'value' => '20']])
        ->pluck('name')->all();

    expect($names)->toEqualCanonicalizing(['Bob', 'Carol']);
});

it('is idempotent: applying already-normalised rows gives the same result', function () {
    $rows = FilterTestModel::normalizeFilters([
        ['field' => 'name', 'operator' => 'contains', 'value' => "Alice\nBob"], // → ['Alice','Bob'] array
    ]);

    $names = FilterTestModel::query()->applyFilters($rows)->pluck('name')->all();

    expect($names)->toEqualCanonicalizing(['Alice', 'Bob']);
});

it('exposes activeFilters() as an alias of normalizeFilters()', function () {
    $raw = [['field' => 'score', 'operator' => 'equals', 'value' => '10']];

    expect(FilterTestModel::activeFilters($raw))
        ->toBe(FilterTestModel::normalizeFilters($raw))
        ->toBe([['field' => 'score', 'operator' => 'equals', 'value' => 10.0]]);
});

it('applies a multi-value set filter', function () {
    $rows = FilterTestModel::normalizeFilters([
        ['field' => 'status', 'operator' => 'in', 'value' => ['active', 'archived']],
    ]);

    $names = FilterTestModel::query()->applyFilters($rows)->pluck('name')->all();

    expect($names)->toEqualCanonicalizing(['Alice', 'Carol']);
});

it('applies an is_empty filter on a nullable column', function () {
    $rows = FilterTestModel::normalizeFilters([
        ['field' => 'email', 'operator' => 'is_empty'],
    ]);

    $names = FilterTestModel::query()->applyFilters($rows)->pluck('name')->all();

    expect($names)->toBe(['Bob']);
});

it('ANDs multiple filter rows together', function () {
    $rows = FilterTestModel::normalizeFilters([
        ['field' => 'status', 'operator' => 'not_equals', 'value' => 'active'],
        ['field' => 'score', 'operator' => 'less_than', 'value' => 30],
    ]);

    $names = FilterTestModel::query()->applyFilters($rows)->pluck('name')->all();

    expect($names)->toBe(['Bob']);
});

it('exposes the field definitions wire contract', function () {
    $fields = FilterTestModel::filterFieldsForFrontend();

    expect($fields)->toHaveCount(5)
        ->and($fields[0])->toMatchArray(['key' => 'name', 'label' => 'Name', 'type' => 'string'])
        ->and($fields[0]['clauses'])->toContain('contains');
});

it('lists allowed filter fields', function () {
    expect(FilterTestModel::allowedFilterFields())
        ->toBe(['name', 'email', 'status', 'score', 'published_at']);
});
