<?php

namespace HXM\LaravelPlanning\Contracts;

use HXM\LaravelPlanning\Models\PlanCondition;

interface ConditionConfigInterface
{
    function getCondition(): ?PlanCondition;
}
