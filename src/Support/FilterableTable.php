<?php

namespace Laratribe\AdvancedFilters\Support;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Laratribe\AdvancedFilters\Concerns\HasFilters;
use Laratribe\AdvancedFilters\Contracts\Filterable;

/**
 * Thin controller-side helper: request → normalise → apply → paginate in one chain.
 *
 *     $table = FilterableTable::for(Campaign::class)
 *         ->query(fn ($q) => $q->withMetrics())   // optional custom base query
 *         ->fromRequest($request);
 *
 *     return view('campaigns.index', [
 *         'campaigns'     => $table->paginate(50),
 *         'filterFields'  => $table->fieldDefinitions(),
 *         'activeFilters' => $table->activeFilters(),
 *     ]);
 *
 * It is additive sugar over {@see HasFilters} — drop down
 * to the trait directly whenever you need finer control.
 *
 * @phpstan-type FilterRow array{field: string, operator: string, value?: mixed, valueTo?: mixed}
 */
class FilterableTable
{
    /** @var class-string<Filterable> */
    protected string $model;

    /** @var Closure(Builder<Model>): Builder<Model>|null */
    protected ?Closure $queryCallback = null;

    /** @var array<int, FilterRow> */
    protected array $filters = [];

    /**
     * @param  class-string  $model
     */
    final public function __construct(string $model)
    {
        if (! is_subclass_of($model, Filterable::class)) {
            throw new InvalidArgumentException(
                "[{$model}] must implement ".Filterable::class.' (use the HasFilters trait).'
            );
        }

        /** @var class-string<Filterable> $model */
        $this->model = $model;
    }

    /**
     * @param  class-string  $model
     */
    public static function for(string $model): static
    {
        return new static($model);
    }

    /**
     * Override the base query (joins, selects, eager loads, metric aggregates).
     *
     * @param  Closure(Builder<Model>): Builder<Model>  $callback
     */
    public function query(Closure $callback): static
    {
        $this->queryCallback = $callback;

        return $this;
    }

    /**
     * Read + normalise filter rows from the request (defaults to the configured query key).
     */
    public function fromRequest(Request $request, ?string $key = null): static
    {
        $key ??= (string) config('advanced-filters.query_key', 'column_filters');

        return $this->withFilters($request->input($key));
    }

    /**
     * Supply raw (un-normalised) filter rows directly — used by Livewire / programmatic callers.
     */
    public function withFilters(mixed $raw): static
    {
        $this->filters = ($this->model)::normalizeFilters($raw);

        return $this;
    }

    /**
     * The normalised, validated filter rows (safe to echo back to the frontend).
     *
     * @return array<int, FilterRow>
     */
    public function activeFilters(): array
    {
        return $this->filters;
    }

    /**
     * The field definitions for the frontend (the wire contract OUT).
     *
     * @return list<array<string, mixed>>
     */
    public function fieldDefinitions(): array
    {
        return ($this->model)::filterFieldsForFrontend();
    }

    /**
     * The filtered query builder, ready for further sorting / pagination.
     *
     * @return Builder<Model>
     */
    public function builder(): Builder
    {
        $query = $this->queryCallback !== null
            ? ($this->queryCallback)(($this->model)::query())
            : ($this->model)::query();

        return $query->applyFilters($this->filters);
    }

    /**
     * @param  array<int, string>  $columns
     * @return LengthAwarePaginator<Model>
     */
    public function paginate(int $perPage = 25, array $columns = ['*'], string $pageName = 'page', ?int $page = null): LengthAwarePaginator
    {
        return $this->builder()
            ->paginate($perPage, $columns, $pageName, $page)
            ->withQueryString();
    }

    /**
     * @param  array<int, string>  $columns
     * @return Collection<int, Model>
     */
    public function get(array $columns = ['*']): Collection
    {
        return $this->builder()->get($columns);
    }
}
