<?php

namespace HXM\LaravelPlanning\Facades;

use HXM\LaravelPlanning\LaravelPlanningManager;
use Illuminate\Support\Facades\Facade;

class LaravelPlanning extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LaravelPlanningManager::class;
    }
}
