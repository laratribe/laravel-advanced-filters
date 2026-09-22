<?php

namespace Laratribe\AdvancedFilters\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laratribe\AdvancedFilters\Concerns\HasFilters;
use Laratribe\AdvancedFilters\Contracts\FilterContract;

/**
 * A reusable collection of filters and the engine that normalises request input,
 * applies it to a query, and serialises the field definitions for the frontend.
 *
 * Both {@see HasFilters} (model-bound) and standalone
 * use cases (reports, raw query builders, preconfigured metric sets) build on this
 * single implementation so behaviour never drifts.
 *
 * @phpstan-type FilterRow array{field: string, operator: string, value?: mixed, valueTo?: mixed}
 */
class FilterSet
{
    /** @var array<string, FilterContract> */
    protected array $keyed;

    /**
     * @param  list<FilterContract>  $filters
     */
    public function __construct(protected array $filters)
    {
        $this->keyed = [];
        foreach ($filters as $filter) {
            $this->keyed[$filter->key()] = $filter;
        }
    }

    /**
     * @param  list<FilterContract>  $filters
     */
    public static function make(array $filters): static
    {
        return new static($filters);
    }

    /**
     * @return list<FilterContract>
     */
    public function all(): array
    {
        return $this->filters;
    }

    /**
     * @return array<string, FilterContract>
     */
    public function keyed(): array
    {
        return $this->keyed;
    }

    /**
     * @return list<string>
     */
    public function allowedFields(): array
    {
        return array_keys($this->keyed);
    }

    public function supports(string $field, string $operator): bool
    {
        $definition = $this->keyed[$field] ?? null;

        return $definition !== null && $definition->supportsClause($operator);
    }

    /**
     * Column filter definitions for the frontend (the wire contract OUT).
     *
     * @return list<array<string, mixed>>
     */
    public function filterDefinitions(): array
    {
        return array_map(
            fn (FilterContract $filter) => $this->serializeField($filter),
            $this->filters
        );
    }

    /**
     * Each filter owns its own wire payload via toArray(), so a custom filter type
     * can ship whatever extra keys its input view needs without a hook here.
     *
     * @return array<string, mixed>
     */
    protected function serializeField(FilterContract $filter): array
    {
        return $filter->toArray();
    }

    /**
     * Validate and normalise raw filter input from the request (the wire contract IN).
     *
     * @return array<int, FilterRow>
     */
    public function normalize(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $out = [];

        foreach ($raw as $row) {
            if (! is_array($row)) {
                continue;
            }

            $field = isset($row['field']) ? (string) $row['field'] : '';
            $operator = isset($row['operator']) ? (string) $row['operator'] : '';

            $definition = $this->keyed[$field] ?? null;
            if ($definition === null) {
                continue;
            }

            if (! $definition->supportsClause($operator)) {
                continue;
            }

            $validated = $definition->validate($operator, $row['value'] ?? null, $row['valueTo'] ?? null);
            if ($validated === null) {
                continue;
            }

            $out[] = array_merge(['field' => $field, 'operator' => $operator], $validated);
        }

        return $out;
    }

    /**
     * Apply normalised filter rows to the query builder.
     *
     * @param  Builder<Model>  $query
     * @param  array<int, FilterRow>  $filters
     * @return Builder<Model>
     */
    public function apply(Builder $query, array $filters): Builder
    {
        if ($filters === []) {
            return $query;
        }

        foreach ($filters as $filter) {
            $definition = $this->keyed[$filter['field']] ?? null;
            if ($definition === null) {
                continue;
            }

            $query = $definition->applyToQuery(
                $query,
                $filter['operator'],
                $filter['value'] ?? null,
                $filter['valueTo'] ?? null,
            );
        }

        return $query;
    }
}
