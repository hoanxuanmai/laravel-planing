<?php

namespace HXM\LaravelPlanning\Contracts;

use \Illuminate\Database\Eloquent\Relations\HasMany;
use \Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use DateTimeInterface;

interface PlanResourceInstance
{
    public function getTotalCycle(): int;

    public function getInterval(): string;

    public function getPlanStartedAt(): ?DateTimeInterface;

    public function planOrder(): MorphOne;

    public function planCycles(): HasMany;
}
