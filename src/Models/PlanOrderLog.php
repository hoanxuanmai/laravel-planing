<?php

namespace HXM\LaravelPlanning\Models;

use HXM\LaravelPlanning\Facades\LaravelPlanning;
use Illuminate\Database\Eloquent\Model;

/**
 * @property Model $referable
 * @property mixed $content
 */
class PlanOrderLog extends Model
{
    const UPDATED_AT = null;

    public $incrementing = false;
    protected $fillable = ['plan_order_id', 'referable_type', 'referable_id', 'content'];

    protected $casts = ['content' => 'array'];

    function referable()
    {
        return $this->morphTo();
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return LaravelPlanning::getTable('orderLog');
    }
}
