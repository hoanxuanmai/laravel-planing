<?php

namespace HXM\LaravelPlanning\Traits\Concerns;

trait HasIntervalCountAttribute
{

    function getIntervalCount(): int
    {
        return ($this->attributes['interval_count'] ?? 0) ?: 1;
    }
}
