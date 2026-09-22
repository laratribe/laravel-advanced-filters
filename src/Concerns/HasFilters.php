<?php

namespace Laratribe\AdvancedFilters\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Laratribe\AdvancedFilters\Contracts\FilterContract;
use Laratribe\AdvancedFilters\Support\FilterSet;

/**
 * Plug-and-play trait for any Eloquent model that needs advanced column filtering.
 *
 * Models implement {@see filters()} to declare their filterable attributes using
 * filter classes (TextFilter, SetFilter, NumericFilter, DateFilter). All behaviour
 * delegates to {@see FilterSet} so the model-bound and standalone paths share one
 * implementation.
 *
 * @phpstan-type FilterRow array{field: string, operator: string, value?: mixed, valueTo?: mixed}
 */
trait HasFilters
{
    /**
     * @return list<FilterContract>
     */
    abstract public static function filters(): array;

    public static function filterSet(): FilterSet
    {
        return FilterSet::make(static::filters());
    }

    /**
     * @return array<string, FilterContract>
     */
    public static function filtersKeyed(): array
    {
        return static::filterSet()->keyed();
    }

    /**
     * @return list<string>
     */
    public static function allowedFilterFields(): array
    {
        return static::filterSet()->allowedFields();
    }

    /**
     * Column filter definitions for the column-filter UI (the wire contract OUT).
     *
     * @return list<array<string, mixed>>
     */
    public static function filterFieldsForFrontend(): array
    {
        return static::filterSet()->fieldDefinitions();
    }

    /**
     * Validate and normalise raw filter input from the request (the wire contract IN).
     *
     * @return array<int, FilterRow>
     */
    public static function normalizeFilters(mixed $raw): array
    {
        return static::filterSet()->normalize($raw);
    }

    /**
     * Alias of {@see normalizeFilters()} — the cleaned, validated rows to hand back to the
     * frontend as the active filters (mirrors FilteredQuery::activeFilters()).
     *
     * @return array<int, FilterRow>
     */
    public static function activeFilters(mixed $raw): array
    {
        return static::normalizeFilters($raw);
    }

    /**
     * Apply filter rows to the query.
     *
     * Accepts either raw request input or already-normalised rows — it normalises
     * internally, so calling normalizeFilters() first is optional. Normalisation is
     * idempotent, so passing already-clean rows is a harmless no-op.
     *
     * @param  Builder<static>  $query
     * @param  mixed  $filters  Raw request input or normalised FilterRow[]
     * @return Builder<static>
     */
    public function scopeApplyFilters(Builder $query, mixed $filters): Builder
    {
        $set = static::filterSet();

        return $set->apply($query, $set->normalize($filters));
    }
}
