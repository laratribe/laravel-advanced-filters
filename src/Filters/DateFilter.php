<?php

namespace Laratribe\AdvancedFilters\Filters;

use Illuminate\Database\Eloquent\Builder;

class DateFilter extends BaseFilter
{
    public function type(): string
    {
        return 'date';
    }

    protected function defaultClauses(): array
    {
        return [
            Clause::Equals,
            Clause::GreaterThan,
            Clause::LessThan,
            Clause::Between,
        ];
    }

    protected function defaultApply(Builder $query, Clause $clause, mixed $value, mixed $valueTo): Builder
    {
        return match ($clause) {
            Clause::Equals => is_string($value) && $value !== ''
                ? $query->whereDate($this->column, '=', $value)
                : $query,
            Clause::GreaterThan => is_string($value) && $value !== ''
                ? $query->where($this->column, '>', $value)
                : $query,
            Clause::LessThan => is_string($value) && $value !== ''
                ? $query->where($this->column, '<', $value)
                : $query,
            Clause::Between => is_string($value) && is_string($valueTo) && $value !== '' && $valueTo !== ''
                ? $query->whereBetween($this->column, [$value, $valueTo])
                : $query,
            default => $query,
        };
    }

    protected function defaultValidate(string $operator, mixed $value, mixed $valueTo): ?array
    {
        if ($operator === Clause::Between->value) {
            $a = self::parseDateString($value);
            $b = self::parseDateString($valueTo);

            return ($a !== null && $b !== null) ? ['value' => $a, 'valueTo' => $b] : null;
        }

        $single = self::parseDateString($value);

        return $single !== null ? ['value' => $single] : null;
    }

    private static function parseDateString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $s = trim($value);

        return ($s !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) ? $s : null;
    }
}
