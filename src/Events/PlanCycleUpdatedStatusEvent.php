<?php

namespace HXM\LaravelPlanning\Events;

use HXM\LaravelPlanning\Models\PlanCycle;

class PlanCycleUpdatedStatusEvent
{
    /**
     * Summary of planCycle
     * @var PlanCycle
     */
    public $planCycle;

    /**
     * Summary of newStatus
     * @var int
     */
    public $preStatus;


    function __construct(PlanCycle $planCycle, int $preStatus)
    {
        $this->planCycle = $planCycle;
        $this->preStatus = $preStatus;
    }
}
