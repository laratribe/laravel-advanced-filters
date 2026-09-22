<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laratribe\AdvancedFilters\Concerns\HasFilters;
use Laratribe\AdvancedFilters\Contracts\Filterable;
use Laratribe\AdvancedFilters\Filters\Clause;
use Laratribe\AdvancedFilters\Filters\CustomClause;
use Laratribe\AdvancedFilters\Filters\DateFilter;
use Laratribe\AdvancedFilters\Filters\NumericFilter;
use Laratribe\AdvancedFilters\Filters\SetFilter;
use Laratribe\AdvancedFilters\Filters\TextFilter;
use Workbench\App\Filters\BooleanFilter;

class Product extends Model implements Filterable
{
    use HasFilters;

    public $timestamps = false;

    protected $guarded = [];

    public static function filters(): array
    {
        return [
            TextFilter::make('name', 'Name'),
            TextFilter::make('sku', 'SKU'),
            SetFilter::make('category', 'Category')->options([
                'electronics' => 'Electronics',
                'books' => 'Books',
                'clothing' => 'Clothing',
                'toys' => 'Toys',
            ])->multiple(),
            SetFilter::make('status', 'Status')->options([
                'in_stock' => 'In stock',
                'low' => 'Low stock',
                'out' => 'Out of stock',
            ]),
            NumericFilter::make('price', 'Price'),
            NumericFilter::make('stock', 'Stock'),

            // A custom operator, defined inline — no subclass, no package change.
            DateFilter::make('released_at', 'Released')->clauses([
                Clause::Equals,
                Clause::Between,
                CustomClause::make('within_last_days', 'Within last N days')
                    ->apply(fn (Builder $q, string $column, $value) => $q->where($column, '>=', now()->subDays((int) $value)->toDateString()))
                    ->validate(fn ($value) => is_numeric($value) && (int) $value > 0 ? ['value' => (int) $value] : null),
            ]),

            // A custom filter type, defined in the app (see Workbench\App\Filters\BooleanFilter).
            BooleanFilter::make('featured', 'Featured'),
        ];
    }
}
