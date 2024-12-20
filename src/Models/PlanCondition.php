<?php

namespace HXM\LaravelPlanning\Models;

use HXM\LaravelPlanning\Facades\LaravelPlanning;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $resource
 * @property string $attribute
 * @property string $operation
 * @property string $value
 */
class PlanCondition extends Model
{

    protected $fillable = ['resource', 'attribute', 'operation', 'value'];

    function target()
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
        return LaravelPlanning::getTable('condition');
    }
}
