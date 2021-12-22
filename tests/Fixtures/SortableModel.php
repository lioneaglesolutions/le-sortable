<?php

namespace Lioneagle\LeSortable\Tests\Fixtures;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lioneagle\LeSortable\Contracts\Sortable as SortableInterface;
use Lioneagle\LeSortable\Traits\Sortable;

/**
 * @property int $id
 * @property string $name
 * @property int $priority
 * @property \Illuminate\Support\Carbon $date
 * @mixin \Illuminate\Database\Eloquent\
 * @method static SortableModel create(string[] $array)
 * @method static SortableModel find(int $id)
 * @method static SortableModel first()
 */
class SortableModel extends Model implements SortableInterface
{
    use Sortable;

    protected $casts = [
        'start_date' => 'datetime',
    ];

    public function newSortQuery(): Builder
    {
        return self::query()->whereDate('start_date', $this->start_date?->format('Y-m-d'));
    }
}
