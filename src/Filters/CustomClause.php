<?php

namespace Laratribe\AdvancedFilters\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laratribe\AdvancedFilters\Contracts\ClauseContract;

/**
 * An ad-hoc operator, defined inline on a filter — no subclassing required.
 *
 *     DateFilter::make('published_at', 'Published')
 *         ->clauses([
 *             Clause::Between,
 *             CustomClause::make('within_last_days', 'Within last N days')
 *                 ->apply(fn (Builder $q, string $col, $v) => $q->where($col, '>=', now()->subDays((int) $v)))
 *                 ->validate(fn ($v) => is_numeric($v) ? ['value' => (int) $v] : null),
 *         ]);
 *
 * For an operator you reuse across several filters, implement {@see ClauseContract}
 * on your own backed enum instead — you get one definition and `match` support.
 *
 * The class is readonly, so the fluent setters return new instances.
 */
final readonly class CustomClause implements ClauseContract
{
    /**
     * @param  Closure(Builder<Model>, string, mixed, mixed): (Builder<Model>|null)|null  $applyCallback
     * @param  Closure(mixed, mixed): mixed|null  $validateCallback
     */
    public function __construct(
        private string $operator,
        private ?string $label = null,
        private string $valueShape = self::SHAPE_SINGLE,
        private ?Closure $applyCallback = null,
        private ?Closure $validateCallback = null,
    ) {}

    public static function make(string $operator, ?string $label = null): self
    {
        return new self($operator, $label);
    }

    public function operator(): string
    {
        return $this->operator;
    }

    public function label(): string
    {
        return $this->label ?? Str::headline($this->operator);
    }

    public function valueShape(): string
    {
        return $this->valueShape;
    }

    /**
     * Set how many values the operator takes — one of the ClauseContract SHAPE_* constants.
     */
    public function shape(string $valueShape): self
    {
        return new self($this->operator, $this->label, $valueShape, $this->applyCallback, $this->validateCallback);
    }

    /** Takes no value input at all. */
    public function withoutValue(): self
    {
        return $this->shape(self::SHAPE_NONE);
    }

    /** Takes `value` and `valueTo`. */
    public function withRange(): self
    {
        return $this->shape(self::SHAPE_RANGE);
    }

    /** Takes a list of values. */
    public function withMultiple(): self
    {
        return $this->shape(self::SHAPE_MULTI);
    }

    /**
     * How the operator constrains the query. Return the builder, or nothing to leave it untouched.
     *
     * @param  Closure(Builder<Model>, string, mixed, mixed): (Builder<Model>|null)  $callback
     */
    public function apply(Closure $callback): self
    {
        return new self($this->operator, $this->label, $this->valueShape, $callback, $this->validateCallback);
    }

    /**
     * Validate/normalise the incoming value. Return `['value' => ...]` (or a bare value,
     * which is wrapped for you), or null to drop the filter row.
     *
     * @param  Closure(mixed, mixed): mixed  $callback
     */
    public function validate(Closure $callback): self
    {
        return new self($this->operator, $this->label, $this->valueShape, $this->applyCallback, $callback);
    }

    public function hasApplyCallback(): bool
    {
        return $this->applyCallback !== null;
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function runApply(Builder $query, string $column, mixed $value, mixed $valueTo): Builder
    {
        if ($this->applyCallback === null) {
            return $query;
        }

        return ($this->applyCallback)($query, $column, $value, $valueTo) ?? $query;
    }

    public function hasValidateCallback(): bool
    {
        return $this->validateCallback !== null;
    }

    /**
     * @return array{value?: mixed, valueTo?: mixed}|null
     */
    public function runValidate(mixed $value, mixed $valueTo): ?array
    {
        if ($this->validateCallback === null) {
            return null;
        }

        $result = ($this->validateCallback)($value, $valueTo);

        if ($result === null) {
            return null;
        }

        return is_array($result) ? $result : ['value' => $result];
    }
}
