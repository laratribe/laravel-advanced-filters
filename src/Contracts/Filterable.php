<?php

namespace Laratribe\AdvancedFilters\Contracts;

use Laratribe\AdvancedFilters\Concerns\HasFilters;

/**
 * Implemented by Eloquent models that declare advanced filters.
 *
 * The behaviour is supplied by {@see HasFilters};
 * a model only has to `use HasFilters` and implement {@see filters()}.
 *
 * @phpstan-type FilterRow array{field: string, operator: string, value?: mixed, valueTo?: mixed}
 */
interface Filterable
{
    /**
     * Declare the filterable attributes for this model.
     *
     * @return list<FilterContract>
     */
    public static function filters(): array;

    /**
     * Column filter definitions serialised for the frontend (the wire contract OUT).
     *
     * @return list<array<string, mixed>>
     */
    public static function filterDefinitions(): array;

    /**
     * Validate and normalise raw filter input from the request (the wire contract IN).
     *
     * @return array<int, FilterRow>
     */
    public static function normalizeFilters(mixed $raw): array;
}
