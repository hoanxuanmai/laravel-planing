<?php

namespace HXM\LaravelPlanning\Traits\Concerns;

use HXM\LaravelPlanning\Models\Plan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use \Illuminate\Database\Eloquent\Relations\MorphOne;
use HXM\LaravelPlanning\Models\PlanCycle;
use HXM\LaravelPlanning\Models\PlanOrder;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;

/**
 * @property PlanOrder $planOrder
 * @property Collection $planCycles
 */

trait HasPlanResourceInstance
{
    function getTotalCycle(): int
    {
        return (int)($this->attributes['cycle_count'] ?? 0);
    }

    function getInterval(): string
    {
        return (string) ($this->attributes['cycle'] ?? 'month');
    }

    function scopeWithCurrentCycle(Builder $query): Builder
    {
        $planCycleModel = new PlanCycle();

        $builder = $planCycleModel->query()->select([$planCycleModel->qualifyColumn('resource_id') . '  as plan_cycle_resource_id',
            $planCycleModel->qualifyColumn('id') . '  as plan_cycle_id',
            $planCycleModel->qualifyColumn('plan_order_id') . '  as plan_order_id',
            $planCycleModel->qualifyColumn('started_at') . ' as plan_cycle_started_at',
            $planCycleModel->qualifyColumn('ended_at' . ' as plan_cycle_ended_at'),
            $planCycleModel->qualifyColumn('status' . ' as plan_cycle_status'),
            ])
            ->where($planCycleModel->qualifyColumn('resource_type'), $this->getMorphClass())
            ->whereDate($planCycleModel->qualifyColumn('started_at'), '<=', now())
            ->whereDate($planCycleModel->qualifyColumn('ended_at'), '>=', now());


        return $query->select([
            $this->qualifyColumn('*'),
            'plan_cycles.*'

        ])->leftJoinSub($builder, 'plan_cycles', $this->qualifyColumn('id'), 'plan_cycles.plan_cycle_resource_id');
    }

    function scopeHasActiveCycle($query)
    {
        return $this->scopeWithCurrentCycle($query)->whereNotNull('plan_cycle_started_at')->where('plan_cycle_status', 1);
    }


    /**
     *
     * @return MorphOne
     */
    function planOrder(): MorphOne
    {
        return $this->morphOne(PlanOrder::class, 'resource');
    }


    /**
     * @return HasMany
     */
    function planCycles(): HasMany
    {
        $planCycleModel = new PlanCycle();
        $query = $planCycleModel->newQuery()
            ->select([
                $planCycleModel->qualifyColumn('*'),
            ])
            ->orderBy($planCycleModel->qualifyColumn('number_of_cycle'), 'desc')
            ->where($planCycleModel->qualifyColumn('resource_type'), $this->getMorphClass());

        return $this->newHasMany(
            $query,
            $this,
            'resource_id',
            'id'
        );
    }
}
