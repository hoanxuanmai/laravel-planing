<?php

namespace HXM\LaravelPlanning\Actions\Updates;

use HXM\LaravelPlanning\Models\PlanCycle;
use Illuminate\Database\Eloquent\Model;

class UpdatePlanCycleStatus
{
    static function handle(PlanCycle $plancycle, int $status = 0, Model $referable = null)
    {
        $plancycle->referable()->associate($referable);
        return $plancycle->update(['status' => $status]);
    }

    static function handleByReferable(Model $referable, int $status = 0)
    {
        $planCycleModel = new PlanCycle();
        $plancycle = PlanCycle::where([
            $planCycleModel->referable()->getMorphType() => $referable->getMorphClass(),
            $planCycleModel->referable()->getForeignKeyName() => $referable->getKey()
        ])->first();
        return $plancycle ? $plancycle->update(['status' => $status]) : false;
    }
}
