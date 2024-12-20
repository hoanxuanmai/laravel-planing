<?php

namespace HXM\LaravelPlanning\Models;

use HXM\LaravelPlanning\Contracts\CycleConfigInterface;
use HXM\LaravelPlanning\Contracts\PlanInstanceInterface;
use HXM\LaravelPlanning\Facades\LaravelPlanning;
use HXM\LaravelPlanning\Traits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * @property string $code
 * @property string $name
 * @property string $description
 * @property string $resource
 * @property int $cycle
 * @property string $interval day,week,month,year
 * @property int $interval_count
 * @property Collection $items
 * @property Collection $orders
 */
class Plan extends Model implements PlanInstanceInterface, CycleConfigInterface
{
    use Traits\Concerns\HasCondition;
    use Traits\Concerns\HasCycleAttribute;
    use Traits\Concerns\HasIntervalAttribute;
    use Traits\Concerns\HasIntervalAttribute;
    use Traits\Concerns\HasIntervalCountAttribute;
    use Traits\Concerns\HasItems;
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'description', 'resource', 'cycle', 'interval', 'interval_count'];

    protected $casts = [
        'cycle' => 'int',
        'interval' => 'string',
        'interval_count' => 'int'
    ];

    function getStartAtCycle(): int
    {
        return 1;
    }
    /**
     * Summary of items
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(PlanItem::class, 'plan_id', 'id');
    }

    public function orders()
    {
        return $this->hasMany(PlanOrder::class, 'plan_id');
    }

    function getOrders(): Collection
    {
        return $this->orders;
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return LaravelPlanning::getTable('plan');
    }
}
