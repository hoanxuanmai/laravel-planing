<?php

namespace HXM\LaravelPlanning\Events;

use HXM\LaravelPlanning\Models\PlanCycle;

class NextPlanCycleCreatedEvent
{
    /**
     * Summary of planCycle
     * @var PlanCycle
     */
    public $planCycle;
    function __construct(PlanCycle $planCycle)
    {
        $this->planCycle = $planCycle;
    }
}
