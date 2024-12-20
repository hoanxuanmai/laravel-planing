<?php

namespace HXM\LaravelPlanning\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface PlanCycleInstanceInterface
{
    public function getPlan(): PlanInstanceInterface;
    public function getPlanOrder(): PlanOrderInstanceInterface;
    public function getReferable(): ?Model;
    public function getItems(): Collection;
}
