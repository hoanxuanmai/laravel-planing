<?php

namespace HXM\LaravelPlanning\Contracts;

use Illuminate\Support\Collection;

interface PlanOrderInstanceInterface extends BasePlanInterface
{
    function getTotalCycle(): int;
    function getItems(): Collection;
    function getPlan(): PlanInstanceInterface;
    function getResource(): PlanResourceInstance;
    function getCycles(): Collection;
    function getLogs(): Collection;
}
