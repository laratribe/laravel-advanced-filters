<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laratribe\AdvancedFilters\Tests\Fixtures\FilterTestModel;
use Laratribe\AdvancedFilters\Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit', 'Blade', 'Livewire');

/**
 * Create the fixture table for integration tests.
 */
function migrateFilterTable(): void
{
    Schema::create('filter_test_records', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->nullable();
        $table->string('status')->default('active');
        $table->float('score')->default(0);
        $table->date('published_at')->nullable();
    });
}

/**
 * @param  array<int, array<string, mixed>>  $rows
 */
function seedFilterRecords(array $rows): void
{
    foreach ($rows as $row) {
        FilterTestModel::create($row);
    }
}
