<?php

namespace Laratribe\AdvancedFilters\Tests\Fixtures;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laratribe\AdvancedFilters\Concerns\HasFilters;
use Laratribe\AdvancedFilters\Contracts\Filterable;
use Laratribe\AdvancedFilters\Filters\Clause;
use Laratribe\AdvancedFilters\Filters\CustomClause;
use Laratribe\AdvancedFilters\Filters\TextFilter;

/**
 * Shares the FilterTestModel table, but declares custom operators — kept separate so
 * the exact field counts asserted against FilterTestModel stay meaningful.
 *
 * @property int $id
 * @property string $name
 * @property string $published_at
 */
class CustomClauseTestModel extends Model implements Filterable
{
    use HasFilters;

    protected $table = 'filter_test_records';

    public $timestamps = false;

    protected $guarded = [];

    public static function filters(): array
    {
        return [
            // The subclass route: a reusable operator baked into a filter type.
            WithinDaysDateFilter::make('published_at', 'Published'),

            // The inline route: an operator defined where it is used, no subclass.
            TextFilter::make('name', 'Name')->clauses([
                Clause::Equals,
                CustomClause::make('sounds_like', 'Sounds like')
                    ->apply(fn (Builder $q, string $column, $value) => $q->where($column, 'like', $value[0].'%'))
                    ->validate(fn ($v) => is_string($v) && $v !== '' ? ['value' => $v] : null),
            ]),
        ];
    }
}
