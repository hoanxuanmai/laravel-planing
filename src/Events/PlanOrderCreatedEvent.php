<?php

namespace HXM\LaravelPlanning\Events;

use HXM\LaravelPlanning\Models\PlanOrder;

class PlanOrderCreatedEvent
{
    /**
     * @var PlanOrder
     */
    public $planOrder;
    public function __construct(PlanOrder $planOrder)
    {
        $this->planOrder = $planOrder;
    }
}
