<?php

namespace HXM\LaravelPlanning\Contracts;

use Illuminate\Support\Collection;

interface BasePlanInterface extends CycleConfigInterface, ConditionConfigInterface
{
    function getInterval(): string;

    function getItems(): Collection;
}
