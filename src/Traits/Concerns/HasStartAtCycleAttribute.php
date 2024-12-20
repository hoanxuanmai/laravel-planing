<?php

namespace HXM\LaravelPlanning\Traits\Concerns;

trait HasStartAtCycleAttribute
{
    function getStartAtCycle(): int
    {
        return $this->attributes['start_at_cycle'] ?? 0;
    }
}
