<?php

namespace Laratribe\AdvancedFilters\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Laratribe\AdvancedFilters\Concerns\HasFilters;
use Laratribe\AdvancedFilters\Contracts\Filterable;
use Laratribe\AdvancedFilters\Filters\DateFilter;
use Laratribe\AdvancedFilters\Filters\NumericFilter;
use Laratribe\AdvancedFilters\Filters\SetFilter;
use Laratribe\AdvancedFilters\Filters\TextFilter;

/**
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property string $status
 * @property float $score
 * @property string $published_at
 */
class FilterTestModel extends Model implements Filterable
{
    use HasFilters;

    protected $table = 'filter_test_records';

    public $timestamps = false;

    protected $guarded = [];

    public static function filters(): array
    {
        return [
            TextFilter::make('name', 'Name'),
            TextFilter::make('email', 'Email')->nullable(),
            SetFilter::make('status', 'Status')->options([
                'active' => 'Active',
                'paused' => 'Paused',
                'archived' => 'Archived',
            ])->multiple(),
            NumericFilter::make('score', 'Score'),
            DateFilter::make('published_at', 'Published'),
        ];
    }
}
