<?php

namespace Laratribe\AdvancedFilters\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laratribe\AdvancedFilters\Contracts\ClauseContract;
use Laratribe\AdvancedFilters\Contracts\FilterContract;

/**
 * @phpstan-type FilterRow array{field: string, operator: string, value?: mixed, valueTo?: mixed}
 */
abstract class BaseFilter implements FilterContract
{
    protected string $column;

    /** @var list<ClauseContract>|null Custom clauses (null = use defaults) */
    protected ?array $customClauses = null;

    protected bool $isNullable = false;

    protected bool $unwrapped = false;

    /** Which input view renders this field (defaults to the filter type). */
    protected ?string $inputName = null;

    /** @var Closure(Builder, string, ClauseContract, mixed): void|null */
    protected ?Closure $applyUsingCallback = null;

    /** @var Closure(mixed, ClauseContract, mixed): mixed|null */
    protected ?Closure $validateUsingCallback = null;

    final public function __construct(
        public readonly string $key,
        public readonly ?string $label = null,
    ) {
        $this->column = $key;
    }

    public static function make(string $key, ?string $label = null): static
    {
        return new static($key, $label);
    }

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return $this->label ?? str_replace('_', ' ', ucfirst($this->key));
    }

    public function column(string $column): static
    {
        $this->column = $column;

        return $this;
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     * Render this field with a different input view than its type would select.
     *
     * The name is matched against the filter type registry, so
     * `TextFilter::make('sku')->input('sku-picker')` keeps string behaviour
     * (validation, coercion, multi-line OR) while swapping only the markup.
     */
    public function input(string $name): static
    {
        $this->inputName = $name;

        return $this;
    }

    public function getInput(): string
    {
        return $this->inputName ?? $this->type();
    }

    /**
     * Override the default clauses for this filter.
     *
     * Accepts built-in {@see Clause} cases and your own {@see ClauseContract}
     * implementations in the same list.
     *
     * @param  list<ClauseContract>  $clauses
     */
    public function clauses(array $clauses): static
    {
        $this->customClauses = $clauses;

        return $this;
    }

    /**
     * Add IsEmpty / IsNotEmpty clauses for nullable attributes.
     */
    public function nullable(): static
    {
        $this->isNullable = true;

        return $this;
    }

    /**
     * Provide a custom callback to apply the filter to the query.
     *
     * @param  Closure(Builder, string, ClauseContract, mixed): void  $callback
     */
    public function applyUsing(Closure $callback, bool $unwrapped = false): static
    {
        $this->applyUsingCallback = $callback;
        $this->unwrapped = $unwrapped;

        return $this;
    }

    /**
     * Prevent the filter from being wrapped in a where() group.
     */
    public function applyUnwrapped(): static
    {
        $this->unwrapped = true;

        return $this;
    }

    /**
     * Provide a custom validation/transformation callback.
     * Return the value if valid, or null to skip the filter.
     *
     * @param  Closure(mixed, ClauseContract, mixed): mixed  $callback
     */
    public function validateUsing(Closure $callback): static
    {
        $this->validateUsingCallback = $callback;

        return $this;
    }

    /**
     * @return list<ClauseContract>
     */
    public function getAvailableClauses(): array
    {
        $clauses = $this->customClauses ?? $this->defaultClauses();

        if ($this->isNullable) {
            // Compare by operator string, not identity — a caller may have supplied
            // their own ClauseContract for `is_empty`.
            $operators = array_map(fn (ClauseContract $c) => $c->operator(), $clauses);

            foreach ([Clause::IsEmpty, Clause::IsNotEmpty] as $clause) {
                if (! in_array($clause->operator(), $operators, true)) {
                    $clauses[] = $clause;
                }
            }
        }

        return $clauses;
    }

    public function supportsClause(string $operator): bool
    {
        return $this->resolveClause($operator) !== null;
    }

    /**
     * Find the clause backing an incoming operator string.
     *
     * Scans the available clauses *only* — never falls back to Clause::tryFrom(),
     * which would resurrect built-in operators on a filter that narrowed its list
     * via clauses(). First match wins if a custom clause shadows a built-in.
     */
    protected function resolveClause(string $operator): ?ClauseContract
    {
        foreach ($this->getAvailableClauses() as $clause) {
            if ($clause->operator() === $operator) {
                return $clause;
            }
        }

        return null;
    }

    /**
     * Apply this filter to the query builder.
     *
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function applyToQuery(Builder $query, string $operator, mixed $value, mixed $valueTo = null): Builder
    {
        $clause = $this->resolveClause($operator);
        if ($clause === null) {
            return $query;
        }

        if ($this->applyUsingCallback !== null) {
            if ($this->unwrapped) {
                ($this->applyUsingCallback)($query, $this->column, $clause, $value);
            } else {
                $query->where(fn (Builder $q) => ($this->applyUsingCallback)($q, $this->column, $clause, $value));
            }

            return $query;
        }

        return $clause instanceof Clause
            ? $this->defaultApply($query, $clause, $value, $valueTo)
            : $this->applyCustomClause($query, $clause, $value, $valueTo);
    }

    /**
     * Validate and normalize a single filter row's value.
     * Returns a normalized array [value => ..., valueTo => ...] or null to skip.
     *
     * @return array{value?: mixed, valueTo?: mixed}|null
     */
    public function validate(string $operator, mixed $value, mixed $valueTo = null): ?array
    {
        $clause = $this->resolveClause($operator);
        if ($clause === null) {
            return null;
        }

        if ($this->validateUsingCallback !== null) {
            $result = ($this->validateUsingCallback)($value, $clause, $valueTo);

            return $result !== null ? (is_array($result) ? $result : ['value' => $result]) : null;
        }

        return $clause instanceof Clause
            ? $this->defaultValidate($operator, $value, $valueTo)
            : $this->validateCustomClause($operator, $clause, $value, $valueTo);
    }

    /**
     * Apply an operator this package doesn't ship.
     *
     * Only ever called for non-{@see Clause} clauses, so an override handles its own
     * operators without having to call parent:: or risk breaking the built-ins.
     *
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    protected function applyCustomClause(Builder $query, ClauseContract $clause, mixed $value, mixed $valueTo): Builder
    {
        if ($clause instanceof CustomClause) {
            return $clause->runApply($query, $this->column, $value, $valueTo);
        }

        return $query;
    }

    /**
     * Validate a value for an operator this package doesn't ship.
     *
     * Defaults to passing the value through rather than returning null: null means
     * "drop this row" in FilterSet::normalize(), which would make a filter that only
     * overrides applyCustomClause() silently do nothing through applyFilters() while
     * still working via a direct applyToQuery() call.
     *
     * @return array{value?: mixed, valueTo?: mixed}|null
     */
    protected function validateCustomClause(string $operator, ClauseContract $clause, mixed $value, mixed $valueTo): ?array
    {
        if ($clause instanceof CustomClause && $clause->hasValidateCallback()) {
            return $clause->runValidate($value, $valueTo);
        }

        if ($clause->valueShape() === ClauseContract::SHAPE_NONE) {
            return [];
        }

        $normalized = ['value' => $value];

        if ($valueTo !== null) {
            $normalized['valueTo'] = $valueTo;
        }

        return $normalized;
    }

    /**
     * Serialize the filter definition for the frontend.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $clauses = $this->getAvailableClauses();

        return [
            'key' => $this->key,
            'label' => $this->label(),
            'type' => $this->type(),
            'input' => $this->getInput(),
            'clauses' => array_map(fn (ClauseContract $c) => $c->operator(), $clauses),
            'clauseItems' => array_map(fn (ClauseContract $c) => [
                'value' => $c->operator(),
                'label' => $c->label(),
                'shape' => $c->valueShape(),
            ], $clauses),
        ];
    }

    /**
     * The filter type identifier sent to the frontend.
     */
    abstract public function type(): string;

    /**
     * @return list<ClauseContract>
     */
    abstract protected function defaultClauses(): array;

    /**
     * Apply one of the built-in {@see Clause} operators.
     *
     * Custom operators never reach here — see {@see applyCustomClause()}.
     *
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    abstract protected function defaultApply(Builder $query, Clause $clause, mixed $value, mixed $valueTo): Builder;

    /**
     * @return array{value?: mixed, valueTo?: mixed}|null
     */
    abstract protected function defaultValidate(string $operator, mixed $value, mixed $valueTo): ?array;
}
