<?php

namespace HXM\LaravelPlanning\Models;

use HXM\LaravelPlanning\Facades\LaravelPlanning;
use Illuminate\Database\Eloquent\Model;

/**
 * @property float $value
 */
class PlanOrderItemPercentPrice extends Model
{
    public $timestamps = false;
    public $incrementing = false;

    protected $primaryKey = 'plan_order_item_id';

    protected $fillable = ['plan_order_item_id', 'parent_item_id', 'value'];

    protected $casts = [
        'value' => 'float'
    ];

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return LaravelPlanning::getTable('orderItemPercentPrice');
    }
}
