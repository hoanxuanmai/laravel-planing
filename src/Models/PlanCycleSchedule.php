<?php

namespace HXM\LaravelPlanning\Models;

use HXM\LaravelPlanning\Contracts\PlanResourceInstance;
use HXM\LaravelPlanning\Facades\LaravelPlanning;
use Illuminate\Database\Eloquent\Model;

class PlanCycleSchedule extends Model
{
    protected $fillable = ['run_at', 'interval', 'number_of_cycle', 'action', 'status', 'message', 'resource_id', 'resource_type', 'plan_order_id'];

    function resource()
    {
        return $this->morphTo('resource');
    }

    function getResource(): ?PlanResourceInstance
    {
        return $this->resource;
    }

    function planOrder()
    {
        return $this->belongsTo(PlanOrder::class, 'plan_order_id');
    }

    function getPlanOrder(): PlanOrder
    {
        return $this->planOrder;
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return LaravelPlanning::getTable('cycleSchedule');
    }

    static function booted()
    {
        static::creating(function (self $model) {
            if (static::query()->where([
                'number_of_cycle' => $model->number_of_cycle,
                'plan_order_id' => $model->plan_order_id,
            ])->exists()) {
                return false;
            }
        });
    }
}
