<?php

namespace HXM\LaravelPlanning\Models;

use HXM\LaravelPlanning\Facades\LaravelPlanning;
use Illuminate\Database\Eloquent\Model;

class PlanCycleItem extends Model
{
    public $timestamps = false;
    protected $fillable = ['id', 'plan_cycle_id', 'plan_order_item_id', 'name', 'description', 'currency', 'price', 'sort'];


    function orderItem()
    {
        return $this->belongsTo(PlanOrderItem::class, 'plan_order_item_id', 'id');
    }

    public function getTable()
    {
        return LaravelPlanning::getTable('cycleItem');
    }
}
