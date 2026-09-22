<?php

namespace Laratribe\AdvancedFilters\Tests\Fixtures;

use Illuminate\Database\Eloquent\Builder;
use Laratribe\AdvancedFilters\Contracts\ClauseContract;
use Laratribe\AdvancedFilters\Filters\CustomClause;
use Laratribe\AdvancedFilters\Filters\DateFilter;

/**
 * A filter subclass that adds one operator the package doesn't ship, the
 * "reusable filter type" way: declare it in defaultClauses() and handle it in
 * applyCustomClause().
 *
 * It deliberately does NOT override validateCustomClause() — the base pass-through
 * has to be enough, or a custom operator would silently vanish in normalize().
 */
class WithinDaysDateFilter extends DateFilter
{
    public const WITHIN_LAST_DAYS = 'within_last_days';

    protected function defaultClauses(): array
    {
        return [
            ...parent::defaultClauses(),
            CustomClause::make(self::WITHIN_LAST_DAYS, 'Within last N days'),
        ];
    }

    protected function applyCustomClause(Builder $query, ClauseContract $clause, mixed $value, mixed $valueTo): Builder
    {
        if ($clause->operator() !== self::WITHIN_LAST_DAYS) {
            return $query;
        }

        return $query->where($this->getColumn(), '>=', now()->subDays((int) $value)->toDateString());
    }
}
