<?php

namespace Laratribe\AdvancedFilters\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TextFilter extends BaseFilter
{
    private const MAX_LINES = 100;

    private const POSITIVE_CLAUSES = [
        Clause::Contains,
        Clause::StartsWith,
        Clause::EndsWith,
        Clause::Equals,
    ];

    protected int $maxLength = 255;

    public function maxLength(int $length): static
    {
        $this->maxLength = $length;

        return $this;
    }

    public function type(): string
    {
        return 'string';
    }

    protected function defaultClauses(): array
    {
        return [
            Clause::Contains,
            Clause::NotContains,
            Clause::StartsWith,
            Clause::EndsWith,
            Clause::Equals,
            Clause::NotEquals,
        ];
    }

    protected function defaultApply(Builder $query, Clause $clause, mixed $value, mixed $valueTo): Builder
    {
        if (is_array($value)) {
            return $this->applyMultipleOr($query, $clause, $value);
        }

        return $this->applySingle($query, $clause, $value);
    }

    private function applySingle(Builder $query, Clause $clause, mixed $value): Builder
    {
        $escaped = is_string($value) ? addcslashes($value, '%_\\') : '';

        return match ($clause) {
            Clause::Contains => $escaped !== ''
                ? $query->where($this->column, 'like', "%{$escaped}%")
                : $query,
            Clause::NotContains => $escaped !== ''
                ? $query->where($this->column, 'not like', "%{$escaped}%")
                : $query,
            Clause::StartsWith => $escaped !== ''
                ? $query->where($this->column, 'like', "{$escaped}%")
                : $query,
            Clause::EndsWith => $escaped !== ''
                ? $query->where($this->column, 'like', "%{$escaped}")
                : $query,
            Clause::Equals => is_string($value) && $value !== ''
                ? $query->where($this->column, '=', $value)
                : $query,
            Clause::NotEquals => is_string($value) && $value !== ''
                ? $query->where($this->column, '!=', $value)
                : $query,
            Clause::IsEmpty => $query->where(function (Builder $q) {
                $q->whereNull($this->column)->orWhere($this->column, '=', '');
            }),
            Clause::IsNotEmpty => $query->where(function (Builder $q) {
                $q->whereNotNull($this->column)->where($this->column, '!=', '');
            }),
            default => $query,
        };
    }

    /**
     * @param  Builder<Model>  $query
     * @param  list<string>  $values
     * @return Builder<Model>
     */
    private function applyMultipleOr(Builder $query, Clause $clause, array $values): Builder
    {
        $column = $this->column;

        return $query->where(function (Builder $q) use ($clause, $values, $column) {
            foreach ($values as $v) {
                if ($v === '') {
                    continue;
                }
                $escaped = addcslashes($v, '%_\\');
                match ($clause) {
                    Clause::Contains => $q->orWhere($column, 'like', "%{$escaped}%"),
                    Clause::StartsWith => $q->orWhere($column, 'like', "{$escaped}%"),
                    Clause::EndsWith => $q->orWhere($column, 'like', "%{$escaped}"),
                    Clause::Equals => $q->orWhere($column, '=', $v),
                    default => null,
                };
            }
        });
    }

    protected function defaultValidate(string $operator, mixed $value, mixed $valueTo): ?array
    {
        if (in_array($operator, [Clause::IsEmpty->value, Clause::IsNotEmpty->value], true)) {
            return [];
        }

        $clause = Clause::tryFrom($operator);
        $isPositive = $clause !== null && in_array($clause, self::POSITIVE_CLAUSES, true);

        // Array input (the multi-line OR result on re-normalisation, or values sent directly
        // by the frontend). Keeps normalisation idempotent.
        if (is_array($value)) {
            $lines = array_values(array_unique(array_filter(
                array_map(fn ($v) => is_string($v) || is_numeric($v) ? mb_substr(trim((string) $v), 0, $this->maxLength) : '', $value),
                fn ($v) => $v !== '',
            )));

            if ($lines === []) {
                return null;
            }

            // OR across multiple values only applies to positive clauses; otherwise use the first.
            if (! $isPositive) {
                return ['value' => $lines[0]];
            }

            return ['value' => count($lines) === 1 ? $lines[0] : array_slice($lines, 0, self::MAX_LINES)];
        }

        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $s = trim((string) $value);
        if ($s === '') {
            return null;
        }

        $hasNewline = preg_match('/\R/', $s) === 1;

        if ($isPositive && $hasNewline) {
            $lines = preg_split('/\R/', $s) ?: [];
            $lines = array_values(array_unique(array_filter(
                array_map(fn ($line) => mb_substr(trim((string) $line), 0, $this->maxLength), $lines),
                fn ($line) => $line !== '',
            )));

            if ($lines === []) {
                return null;
            }

            if (count($lines) === 1) {
                return ['value' => $lines[0]];
            }

            return ['value' => array_slice($lines, 0, self::MAX_LINES)];
        }

        return ['value' => mb_substr($s, 0, $this->maxLength)];
    }
}
