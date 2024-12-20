<?php

namespace HXM\LaravelPlanning\Contracts;

interface PlanItemInstanceInterface extends BasePlanItemInstanceInterface
{
    function getPlan(): PlanInstanceInterface;
}
