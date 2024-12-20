<?php

namespace HXM\LaravelPlanning\Traits\Concerns;

use HXM\LaravelPlanning\Models\PlanCondition;

trait HasCondition
{

    public function getCondition(): ?PlanCondition
    {
        return $this->condition;
    }


    /**
     * Laravle Relation
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function condition()
    {
        return $this->morphOne(PlanCondition::class, 'target');
    }
}
