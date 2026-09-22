<?php

namespace Laratribe\AdvancedFilters\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SetFilter extends BaseFilter
{
    /** @var array<string, string> key => label */
    protected array $options = [];

    protected bool $isMultiple = false;

    protected bool $hideClause = false;

    /**
     * Set the available options.
     * Accepts key=>label pairs or a flat list (values used as both key and label).
     *
     * @param  array<string, string>|list<string>  $options
     */
    public function options(array $options): static
    {
        if (array_is_list($options)) {
            $this->options = array_combine($options, $options);
        } else {
            $this->options = $options;
        }

        return $this;
    }

    /**
     * Populate options from an Eloquent model.
     *
     * @param  class-string<Model>  $model
     */
    public function pluckOptionsFromModel(string $model, string $labelColumn, ?string $keyColumn = null): static
    {
        $keyColumn ??= (new $model)->getKeyName();
        $this->options = $model::query()->pluck($labelColumn, $keyColumn)->all();

        return $this;
    }

    /**
     * Default this filter to selecting several values at once (an OR match).
     *
     * It promotes the In / NotIn clauses to the front of the list, and since the
     * panel preselects the first clause, picking the column lands you straight on a
     * multi-select. Equals / NotEquals stay available behind the operator dropdown.
     */
    public function multiple(): static
    {
        $this->isMultiple = true;

        return $this;
    }

    /**
     * Hide the clause selector in the frontend; always use Equals.
     */
    public function withoutClause(): static
    {
        $this->hideClause = true;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getAllowedValues(): array
    {
        return array_map('strval', array_keys($this->options));
    }

    public function type(): string
    {
        return 'select';
    }

    protected function defaultClauses(): array
    {
        if ($this->hideClause) {
            return [$this->isMultiple ? Clause::In : Clause::Equals];
        }

        // The panel preselects the first clause, so ordering decides which input the
        // user lands on — a multi-select for multiple(), a single select otherwise.
        return $this->isMultiple
            ? [Clause::In, Clause::NotIn, Clause::Equals, Clause::NotEquals]
            : [Clause::Equals, Clause::NotEquals, Clause::In, Clause::NotIn];
    }

    protected function defaultApply(Builder $query, Clause $clause, mixed $value, mixed $valueTo): Builder
    {
        $allowed = $this->getAllowedValues();

        return match ($clause) {
            Clause::Equals => is_string($value) && in_array($value, $allowed, true)
                ? $query->where($this->column, '=', $value)
                : $query,
            Clause::NotEquals => is_string($value) && in_array($value, $allowed, true)
                ? $query->where($this->column, '!=', $value)
                : $query,
            Clause::In => $this->applyInClause($query, $value, $allowed),
            Clause::NotIn => $this->applyNotInClause($query, $value, $allowed),
            default => $query,
        };
    }

    protected function defaultValidate(string $operator, mixed $value, mixed $valueTo): ?array
    {
        $allowed = $this->getAllowedValues();

        if ($operator === Clause::Equals->value || $operator === Clause::NotEquals->value) {
            $v = is_string($value) ? $value : '';

            return in_array($v, $allowed, true) ? ['value' => $v] : null;
        }

        $list = is_array($value) ? $value : [$value];
        $clean = [];

        foreach ($list as $v) {
            if (is_string($v) && in_array($v, $allowed, true)) {
                $clean[] = $v;
            }
        }

        $clean = array_values(array_unique($clean));

        return $clean !== [] ? ['value' => $clean] : null;
    }

    /**
     * Options reach the frontend as an ordered list of {value, label} pairs — JSON
     * objects don't guarantee key order, and numeric keys would come back as ints.
     */
    public function toArray(): array
    {
        $optionItems = [];

        foreach ($this->options as $value => $label) {
            $optionItems[] = [
                'value' => (string) $value,
                'label' => is_string($label) ? $label : (string) $value,
            ];
        }

        return array_merge(parent::toArray(), [
            'optionItems' => $optionItems,
            'multiple' => $this->isMultiple,
            'hideClause' => $this->hideClause,
        ]);
    }

    /**
     * @param  Builder<Model>  $query
     * @param  list<string>  $allowed
     * @return Builder<Model>
     */
    private function applyInClause(Builder $query, mixed $value, array $allowed): Builder
    {
        $values = $this->filterAllowed($value, $allowed);

        return $values !== [] ? $query->whereIn($this->column, $values) : $query;
    }

    /**
     * @param  Builder<Model>  $query
     * @param  list<string>  $allowed
     * @return Builder<Model>
     */
    private function applyNotInClause(Builder $query, mixed $value, array $allowed): Builder
    {
        $values = $this->filterAllowed($value, $allowed);

        return $values !== [] ? $query->whereNotIn($this->column, $values) : $query;
    }

    /**
     * @param  list<string>  $allowed
     * @return list<string>
     */
    private function filterAllowed(mixed $value, array $allowed): array
    {
        $list = is_array($value) ? $value : [$value];
        $out = [];

        foreach ($list as $v) {
            if (is_string($v) && in_array($v, $allowed, true)) {
                $out[] = $v;
            }
        }

        return array_values(array_unique($out));
    }
}
