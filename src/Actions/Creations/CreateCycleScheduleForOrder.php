<?php

namespace HXM\LaravelPlanning\Actions\Creations;

use HXM\LaravelPlanning\Constants\IntervalTypes;
use HXM\LaravelPlanning\Models\PlanCycleSchedule;
use HXM\LaravelPlanning\Models\PlanOrder;

class CreateCycleScheduleForOrder
{
    static function handle(PlanOrder $order, int $numberOfCycle = null)
    {
        $action = new CreateNextCycleFromPlanOrderCalculator($order->getResource(), $order);
        $cycle = $action->handle($numberOfCycle);
        $runAt = clone $cycle->started_at;
        switch ($order->getInterVal()) {
            case IntervalTypes::WEEK:
                $runAt->subDays(2);
                break;
            case IntervalTypes::MONTH:
                $runAt->subDays(5);
                break;
            case IntervalTypes::YEAR:
                $runAt->subDays(20);
                break;
        }

        $schedule = new PlanCycleSchedule([
            'number_of_cycle' => $cycle->getNumberOfCycle(),
            'interval' => $order->getInterVal(),
            'status' => 0,
            'run_at' => $runAt
        ]);

        $schedule->planOrder()->associate($order);
        $schedule->resource()->associate($order->getResource());
        if ($schedule->save()) {
            $order->addLog("Created schedule for number of cycle #{$schedule->number_of_cycle}", $schedule);
        } else {
            $order->addLog("Can not create schedule for number of cycle #{$schedule->number_of_cycle}", $schedule);
        }

        return $schedule;
    }
}
