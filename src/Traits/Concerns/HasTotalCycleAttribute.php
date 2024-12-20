<?php

namespace HXM\LaravelPlanning\Traits\Concerns;

trait HasTotalCycleAttribute
{
    function getTotalCycle(): int
    {
        return $this->attributes['total_cycle'] ?? 0;
    }
}
