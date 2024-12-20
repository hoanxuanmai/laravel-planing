<?php

namespace HXM\LaravelPlanning\Contracts;

interface CycleConfigInterface
{
    function isCycle(): bool;

    function getCycle(): int;

    function getIntervalCount(): int;

    function getStartAtCycle(): int;
}
