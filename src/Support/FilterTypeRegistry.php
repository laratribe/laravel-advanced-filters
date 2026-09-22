<?php

namespace Laratribe\AdvancedFilters\Support;

use InvalidArgumentException;

/**
 * Maps a filter type (the `input` key of the wire contract) to the Blade view that
 * renders its value input.
 *
 * The panels iterate this registry instead of hard-coding @include lines, so adding
 * a filter type never requires forking a shipped view:
 *
 *     AdvancedFilters::registerType('boolean', 'filters.boolean');
 *
 * Publishing still overrides. The built-ins are registered under the
 * `advanced-filters::` namespace, and Laravel's view finder already prefers
 * resources/views/vendor/advanced-filters — so publishing replaces markup, while
 * registering adds to it.
 */
class FilterTypeRegistry
{
    /** @var array<string, string> type => view name */
    protected array $types = [];

    /**
     * Register (or replace) the input view for a type. Re-registering an existing
     * type swaps its view and keeps its position in the render order.
     */
    public function register(string $type, string $view): static
    {
        if ($type === '' || $view === '') {
            throw new InvalidArgumentException('A filter type and its view name must both be non-empty.');
        }

        $this->types[$type] = $view;

        return $this;
    }

    /**
     * @param  array<string, string>  $types  type => view name
     */
    public function registerMany(array $types): static
    {
        foreach ($types as $type => $view) {
            $this->register($type, $view);
        }

        return $this;
    }

    public function forget(string $type): static
    {
        unset($this->types[$type]);

        return $this;
    }

    public function has(string $type): bool
    {
        return isset($this->types[$type]);
    }

    public function viewFor(string $type): ?string
    {
        return $this->types[$type] ?? null;
    }

    /**
     * Every registered input view, in registration order, deduped.
     *
     * Two types may legitimately share one view (a partial that switches internally),
     * so the same view is only rendered once.
     *
     * @return list<string>
     */
    public function inputViews(): array
    {
        return array_values(array_unique($this->types));
    }

    /**
     * The input views for a specific subset of types, in registration order.
     * Unknown types are skipped.
     *
     * @param  list<string>  $types
     * @return list<string>
     */
    public function inputViewsFor(array $types): array
    {
        $views = [];

        foreach ($this->types as $type => $view) {
            if (in_array($type, $types, true)) {
                $views[] = $view;
            }
        }

        return array_values(array_unique($views));
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->types;
    }
}
