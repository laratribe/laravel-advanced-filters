<?php

namespace Laratribe\AdvancedFilters\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NumericFilter extends BaseFilter
{
    /**
     * When set, filter uses HAVING with this raw SQL expression instead of WHERE on column.
     * Typically a metric expression like "COALESCE(SUM(m.clicks), 0)".
     *
     * The expression is developer-supplied SQL and is NEVER user input — values are
     * always bound as parameters. Do not interpolate request data into it.
     */
    protected ?string $havingExpr = null;

    /**
     * Switch this filter to use HAVING with the given SQL expression.
     */
    public function havingExpression(string $expression): static
    {
        $this->havingExpr = $expression;

        return $this;
    }

    public function usesHaving(): bool
    {
        return $this->havingExpr !== null;
    }

    public function type(): string
    {
        return 'number';
    }

    protected function defaultClauses(): array
    {
        return [
            Clause::Equals,
            Clause::NotEquals,
            Clause::GreaterThan,
            Clause::LessThan,
            Clause::GreaterThanOrEqual,
            Clause::LessThanOrEqual,
            Clause::Between,
        ];
    }

    protected function defaultApply(Builder $query, Clause $clause, mixed $value, mixed $valueTo): Builder
    {
        if ($this->havingExpr !== null) {
            return $this->applyHaving($query, $clause, $value, $valueTo);
        }

        return $this->applyWhere($query, $clause, $value, $valueTo);
    }

    protected function defaultValidate(string $operator, mixed $value, mixed $valueTo): ?array
    {
        if ($operator === Clause::Between->value) {
            if (! is_numeric($value) || ! is_numeric($valueTo)) {
                return null;
            }

            return ['value' => (float) $value, 'valueTo' => (float) $valueTo];
        }

        if (! is_numeric($value)) {
            return null;
        }

        return ['value' => (float) $value];
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    private function applyWhere(Builder $query, Clause $clause, mixed $value, mixed $valueTo): Builder
    {
        if (! is_numeric($value) && $clause !== Clause::Between) {
            return $query;
        }

        return match ($clause) {
            Clause::Equals => $query->where($this->column, '=', (float) $value),
            Clause::NotEquals => $query->where($this->column, '!=', (float) $value),
            Clause::GreaterThan => $query->where($this->column, '>', (float) $value),
            Clause::LessThan => $query->where($this->column, '<', (float) $value),
            Clause::GreaterThanOrEqual => $query->where($this->column, '>=', (float) $value),
            Clause::LessThanOrEqual => $query->where($this->column, '<=', (float) $value),
            Clause::Between => is_numeric($value) && is_numeric($valueTo)
                ? $query->whereBetween($this->column, [(float) $value, (float) $valueTo])
                : $query,
            default => $query,
        };
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    private function applyHaving(Builder $query, Clause $clause, mixed $value, mixed $valueTo): Builder
    {
        $expr = $this->havingExpr;

        $op = match ($clause) {
            Clause::Equals => '=',
            Clause::NotEquals => '!=',
            Clause::GreaterThan => '>',
            Clause::LessThan => '<',
            Clause::GreaterThanOrEqual => '>=',
            Clause::LessThanOrEqual => '<=',
            Clause::Between => 'BETWEEN',
            default => null,
        };

        if ($op === null) {
            return $query;
        }

        if ($op === 'BETWEEN') {
            if (! is_numeric($value) || ! is_numeric($valueTo)) {
                return $query;
            }

            return $query->havingRaw("{$expr} BETWEEN ? AND ?", [(float) $value, (float) $valueTo]);
        }

        if (! is_numeric($value)) {
            return $query;
        }

        return $query->havingRaw("{$expr} {$op} ?", [(float) $value]);
    }
}
