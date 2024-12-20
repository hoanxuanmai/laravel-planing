<?php

use HXM\LaravelPlanning\Events;
use HXM\LaravelPlanning\Listeners;
use HXM\LaravelPlanning\Models;


return [
    "resources" => [], //string[] resource list
    "useMigration" => true,
    "tables" => [
        "prefix" => '',
        "plan" => 'plans',
        "item" => 'plan_items',
        "itemPercentPrice" => 'plan_item_percent_prices',
        "condition" => 'plan_conditions',
        "order" => 'plan_orders',
        "orderItem" => 'plan_order_items',
        "orderItemPercentPrice" => 'plan_order_item_percent_prices',
        "orderLog" => 'plan_order_logs',
        "cycle" => 'plan_cycles',
        "cycleItem" => 'plan_cycle_items',
        "cycleSchedule" => 'plan_schedules',
    ],
    "models" => [
        "plan" => Models\Plan::class,
        "item" => Models\PlanItem::class,
        "itemPercentPrice" => Models\PlanItemPercentPrice::class,
        "condition" => Models\PlanCondition::class,
        "order" => Models\PlanOrder::class,
        "orderItem" => Models\PlanOrderItem::class,
        "orderLog" => Models\PlanOrderLog::class,
        "cycle" => Models\PlanCycle::class,
        "cycleItem" => Models\PlanCycleItem::class,
    ],
    "listeners" => [
        Events\PlanOrderCreatedEvent::class => function (Events\PlanOrderCreatedEvent $event) {
            $event->planOrder->addLog('Created');
        },
        Events\PlanCycleUpdatedStatusEvent::class => [
            Listeners\CreatePlanScheduleWhenCycleChangedStatus::class,
            function (Events\PlanCycleUpdatedStatusEvent $event) {
                $event->planCycle->planOrder->addLog('cycle#' . $event->planCycle->getKey() . ' change Status from ' . $event->preStatus . ' to ' . $event->planCycle->status, $event->planCycle->getReferable());
            }
        ],
    ],
    "schedule" => true,
    "cron" => '10 0 * * *',
    "pannel" => [
        "enable" => true,
        "prefix" => "plans",
        "as" => "plans.",
        "middleware" => ["web", "auth"]
    ]

];
