<?php

namespace HXM\LaravelPlanning\Traits\Concerns;

trait HasCycleAttribute
{
    function isCycle(): bool
    {
        return $this->getCycle() !== 1;
    }

    function getCycle(): int
    {
        return (int)($this->attributes['cycle'] ?? $this->attributes['total_cycle'] ?? 1);
    }
}
