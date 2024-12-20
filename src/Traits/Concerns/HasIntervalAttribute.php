<?php

namespace HXM\LaravelPlanning\Traits\Concerns;

trait HasIntervalAttribute
{

    function isInterval(): bool
    {
        return !empty($this->attributes['interval']);
    }

    function getInterval(): string
    {
        return $this->attributes['interval'] ?? '';
    }
}
