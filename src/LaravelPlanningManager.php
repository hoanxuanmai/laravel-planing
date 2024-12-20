<?php

namespace HXM\LaravelPlanning;

use HXM\LaravelPlanning\Contracts\PlanResourceInstance;
use HXM\LaravelPlanning\Helpers\PlanCycleHelper;
use HXM\LaravelPlanning\Models;
use HXM\LaravelPlanning\Models\PlanOrder;
use Illuminate\Support\Str;


class LaravelPlanningManager
{
    /**
     * Summary of configs
     * @var array
     */
    protected array $configs;

    /**
     *
     * @var bool
     */
    static public $useMigration = false;

    /**
     * Summary of defaults
     * @var array
     */
    protected array $defaults = [
        'tables' => [
            'prefix' => 'hxm_',
            'plan' => 'plans',
            'item' => 'plan_items',
            'itemPercentPrice' => 'plan_item_percent_prices',
            'condition' => 'plan_conditions',
            'order' => 'plan_orders',
            'orderItem' => 'plan_order_items',
            'orderItemPercentPrice' => 'plan_order_item_percent_prices',
            'orderLog' => 'plan_order_logs',
            'cycle' => 'plan_cycles',
            'cycleItem' => 'plan_cycle_items',
        ],
    ];

    /**
     * Summary of resources
     * @var array
     */
    protected array $_resources = [];

    function __construct(array $configs = [])
    {
        $this->configs = $configs;
        $this->addResources($this->getConfig('resources'));
    }

    function getConfig(string $key = null, $default = null)
    {
        return data_get($this->configs, $key, $default);
    }

    function getTable($key): string
    {
        return ($this->configs['tables']['prefix'] ?? '') . ($this->configs['tables'][$key] ?? $this->defaults['tables'][$key] ?? null);
    }

    function getPlanCycleHelperByResource(PlanResourceInstance $resource, PlanOrder $planOrder): PlanCycleHelper
    {
        return PlanCycleHelper::createByCurrentDay($planOrder->getInterval(), $planOrder->getIntervalCount(), $resource->getPlanStartedAt());
    }

    /**
     * Summary of route
     * @param string $name
     * @param mixed $parameters
     * @param bool $absolute
     * @return string
     */
    function route(string $name, $parameters = [], bool $absolute = true)
    {
        $as = config('laravel_planning.pannel.as', 'plans.');
        if (!Str::contains($name, $as)) {
            $name = $as . $name;
        }
        return route($name, $parameters, $absolute);
    }

    function addResources(array $resources)
    {
        $this->_resources = array_unique(array_merge($this->_resources, array_values($resources)));
        return $this;
    }

    function getResources(): array
    {
        return $this->_resources;
    }
}
