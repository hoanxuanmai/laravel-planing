<?php

namespace HXM\LaravelPlanning\Listeners;

use HXM\LaravelPlanning\Actions\Creations\CreateCycleScheduleForOrder;
use HXM\LaravelPlanning\Events\PlanCycleUpdatedStatusEvent;

class CreatePlanScheduleWhenCycleChangedStatus
{
    function handle(PlanCycleUpdatedStatusEvent $event)
    {
        $planCycle = $event->planCycle;
        $planCycle->status == 1 && $planCycle->getPlanOrder()->isCycle() && (new CreateCycleScheduleForOrder())->handle($planCycle->getPlanOrder(), $planCycle->getNumberOfCycle() + 1);
    }
}
