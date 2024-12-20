<?php

namespace HXM\LaravelPlanning\Models;

use HXM\LaravelPlanning\Contracts\CycleConfigInterface;
use HXM\LaravelPlanning\Contracts\PlanInstanceInterface;
use HXM\LaravelPlanning\Contracts\PlanItemInstanceInterface;
use HXM\LaravelPlanning\Facades\LaravelPlanning;
use HXM\LaravelPlanning\Traits\Concerns\HasCondition;
use HXM\LaravelPlanning\Traits\Concerns\HasCycleAttribute;
use HXM\LaravelPlanning\Traits\Concerns\HasIntervalAttribute;
use HXM\LaravelPlanning\Traits\Concerns\HasIntervalCountAttribute;
use HXM\LaravelPlanning\Traits\Concerns\HasStartAtCycleAttribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


/**
 * @property string $name
 * @property string $description
 * @property float $price
 * @property int $cycle
 * @property int $interval_count
 * @property int $start_at_cycle
 * @property int $sort
 * @property Plan $plan
 * @property ?PlanItemPercentPrice $percent_price
 * @property ?PlanCondition $condition
 */
class PlanItem extends Model implements PlanItemInstanceInterface, CycleConfigInterface
{
    use HasStartAtCycleAttribute, HasIntervalCountAttribute, HasCycleAttribute, HasCondition;
    protected $fillable = ['name', 'description', 'price', 'cycle', 'interval_count', 'start_at_cycle', 'sort'];

    protected $casts = [
        'cycle' => 'int',
        'start_at_cycle' => 'int',
        'interval_count' => 'int'
    ];
    protected $with = ['condition', 'percent_price'];

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'id', 'plan_id');
    }

    function getPlan(): PlanInstanceInterface
    {
        return $this->plan;
    }

    public function percent_price()
    {
        return $this->hasOne(PlanItemPercentPrice::class);
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return LaravelPlanning::getTable('item');
    }


    static function booted()
    {
        static::addGlobalScope('order_by_sort_desc', function (Builder $builder) {
            $builder->orderBy('sort');
        });
    }
}
