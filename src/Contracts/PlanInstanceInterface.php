<?php

namespace HXM\LaravelPlanning\Contracts;

use HXM\LaravelPlanning\Models\PlanCondition;
use Illuminate\Support\Collection;

interface PlanInstanceInterface extends BasePlanInterface
{
    function isCycle(): bool;

    function getCycle(): int;

    function getOrders(): Collection;

    function getCondition(): ?PlanCondition;
}
