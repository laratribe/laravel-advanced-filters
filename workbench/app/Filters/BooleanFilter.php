<?php

namespace Workbench\App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Laratribe\AdvancedFilters\Filters\BaseFilter;
use Laratribe\AdvancedFilters\Filters\Clause;

/**
 * A filter type the package doesn't ship, living entirely in the host app.
 *
 * Three pieces, none of which require touching the package:
 *   1. this class          — declares type() = 'boolean'
 *   2. an input view       — workbench/resources/views/filters/boolean.blade.php
 *   3. a registry entry    — AdvancedFilters::register('boolean', ...) in WorkbenchServiceProvider
 *
 * Plus an optional JS handler (see layout.blade.php) when the shape-driven defaults
 * aren't quite right — here, to default the value to "Yes" and label the chip.
 */
class BooleanFilter extends BaseFilter
{
    public function type(): string
    {
        return 'boolean';
    }

    protected function defaultClauses(): array
    {
        return [Clause::Equals];
    }

    protected function defaultApply(Builder $query, Clause $clause, mixed $value, mixed $valueTo): Builder
    {
        return $query->where($this->getColumn(), '=', (int) $value);
    }

    protected function defaultValidate(string $operator, mixed $value, mixed $valueTo): ?array
    {
        return in_array((string) $value, ['0', '1'], true) ? ['value' => (string) $value] : null;
    }
}
