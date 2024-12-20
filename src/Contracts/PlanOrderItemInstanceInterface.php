<?php

namespace HXM\LaravelPlanning\Contracts;

interface PlanOrderItemInstanceInterface extends BasePlanItemInstanceInterface
{
    function getPlan(): BasePlanInterface;
    function getOrder(): PlanOrderInstanceInterface;
}
