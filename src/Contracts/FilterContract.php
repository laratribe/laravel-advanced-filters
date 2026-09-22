<?php

namespace Laratribe\AdvancedFilters\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laratribe\AdvancedFilters\Filters\BaseFilter;

/**
 * The public surface every filter type exposes. {@see BaseFilter}
 * is the canonical implementation; type-hint against this interface when you
 * accept arbitrary filters (e.g. in a FilterSet).
 *
 * @phpstan-type FilterRow array{field: string, operator: string, value?: mixed, valueTo?: mixed}
 */
interface FilterContract
{
    /**
     * The field key this filter is addressed by on the wire.
     */
    public function key(): string;

    /**
     * The human label shown in the column list.
     */
    public function label(): string;

    public function getColumn(): string;

    /**
     * @return list<ClauseContract>
     */
    public function getAvailableClauses(): array;

    public function supportsClause(string $operator): bool;

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function applyToQuery(Builder $query, string $operator, mixed $value, mixed $valueTo = null): Builder;

    /**
     * @return array{value?: mixed, valueTo?: mixed}|null
     */
    public function validate(string $operator, mixed $value, mixed $valueTo = null): ?array;

    public function type(): string;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
