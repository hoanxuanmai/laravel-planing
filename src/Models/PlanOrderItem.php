<?php

namespace HXM\LaravelPlanning\Models;

use HXM\LaravelPlanning\Contracts\CycleConfigInterface;
use HXM\LaravelPlanning\Contracts\PlanOrderItemInstanceInterface;
use HXM\LaravelPlanning\Facades\LaravelPlanning;
use HXM\LaravelPlanning\Traits\Concerns\HasCondition;
use HXM\LaravelPlanning\Traits\Concerns\HasCycleAttribute;
use HXM\LaravelPlanning\Traits\Concerns\HasIntervalCountAttribute;
use HXM\LaravelPlanning\Traits\Concerns\HasStartAtCycleAttribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $name
 * @property string $description
 * @property float $price
 * @property int $cycle
 * @property int $interval_count
 * @property int $start_at_cycle
 * @property int $sort
 * @property PlanOrder $order
 * @property ?PlanCondition $condition
 * @property ?PlanOrderItemPercentPrice $percent_price
 *
 */
class PlanOrderItem extends Model implements PlanOrderItemInstanceInterface, CycleConfigInterface
{
    use HasCycleAttribute, HasIntervalCountAttribute, HasStartAtCycleAttribute, HasCondition;
    use SoftDeletes;

    protected $fillable = ['plan_item_id', 'name', 'description', 'price', 'currency', 'cycle', 'interval_count', 'start_at_cycle', 'sort'];

    protected $casts = [
        'price' => 'float',
        'currency' => 'string',
        'cycle' => 'int',
        'start_at_cycle' => 'int',
        'interval_count' => 'int'
    ];
    protected $with = ['condition'];

    function order()
    {
        return $this->belongsTo(PlanOrder::class);
    }

    function getOrder(): \HXM\LaravelPlanning\Contracts\PlanOrderInstanceInterface
    {
        return $this->order;
    }

    function getPlan(): \HXM\LaravelPlanning\Contracts\BasePlanInterface
    {
        return $this->order;
    }


    public function percent_price()
    {
        return $this->hasOne(PlanOrderItemPercentPrice::class);
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return LaravelPlanning::getTable('orderItem');
    }
    static function booted()
    {
        static::created(function (self $model) {
            $model->relationLoaded('condition') && $model->condition()->save($model->condition);
        });
    }
}
