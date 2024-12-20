<?php

namespace HXM\LaravelPlanning\Actions\Creations;

use \Exception;
use DatePeriod;
use DateTimeInterface;
use HXM\LaravelPlanning\Constants\ComparaseOperation;
use HXM\LaravelPlanning\Contracts\BasePlanInterface;
use HXM\LaravelPlanning\Contracts\CycleConfigInterface;
use HXM\LaravelPlanning\Contracts\PlanInstanceInterface;
use HXM\LaravelPlanning\Contracts\PlanResourceInstance;
use HXM\LaravelPlanning\Helpers\PlanCycleHelper;
use HXM\LaravelPlanning\Models\PlanCondition;
use HXM\LaravelPlanning\Models\PlanCycle;
use HXM\LaravelPlanning\Models\PlanOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;


abstract class BaseCalculator
{
    /**
     * The Resource
     * @var Model&PlanResourceInstance
     */
    protected $resource;

    /**
     * The Plan
     * @var Model&BasePlanInterface
     */
    protected $plan;
    /**
     * The Current Cycle Items
     * @var Collection
     */
    protected $_currentCycleItems;

    /**
     * Summary of planStartedAt
     * @var DateTimeInterface
     */
    protected $planStartedAt;

    /**
     * Summary of cycleStartedAt
     * @var DateTimeInterface
     */
    protected $cycleStartedAt;
    /**
     * Summary of cycleEndedAt
     * @var DateTimeInterface
     */
    protected $cycleEndedAt;

    /**
     * Summary of cycleRange
     * @var DatePeriod
     */
    protected $cycleRange;

    /**
     * Summary of _planCycleHelper
     * @var PlanCycleHelper
     */
    protected $_planCycleHelper;

    /**
     * Summary of __construct
     * @param PlanResourceInstance&Model $resource
     * @param PlanInstanceInterface&Model $plan
     * @throws \Exception
     */
    function __construct(Model $resource, Model $plan)
    {

        if (! $resource instanceof PlanResourceInstance) {
            throw new Exception("The Resource: " . get_class($resource) . " must be instance of " . PlanResourceInstance::class);
        }

        $this->resource = $resource;

        if (! $plan instanceof BasePlanInterface) {
            throw new Exception("The Plan " . get_class($plan) . "must be instance of " . PlanInstanceInterface::class);
        }

        $this->plan = $plan;

        $this->plan->loadMissing('items.condition');
    }

    /**
     * @return Model&PlanInstanceInterface
     */
    public function getPlan(): Model
    {
        return $this->plan;
    }

    /**
     * @return Model&PlanResourceInstance
     */
    public function getResource(): Model
    {
        return $this->resource;
    }

    abstract public function getPlanOrder(): ?PlanOrder;
    abstract public function getPlanCycleHelper(): PlanCycleHelper;

    abstract public function getPlanItems(): Collection;


    function getPlanStartedAt(): DateTimeInterface
    {
        return $this->getPlanCycleHelper()->planStartAt();
    }

    function getCycleStartedAt(): DateTimeInterface
    {
        return $this->getPlanCycleHelper()->cycleStartAt();
    }

    function getCycleEndedAt(): DateTimeInterface
    {

        return $this->getPlanCycleHelper()->cycleEndAt();
    }
    function getNumberOfCycle(): int
    {
        return $this->getPlanCycleHelper()->numberOfCycle();
    }

    protected function checkCondition($item): bool
    {
        $condition = $item->condition;
        if ($condition && $condition instanceof PlanCondition) {

            $resourceValue = $this->resource->getAttributes()[$condition->attribute] ?? null;

            switch ($condition->operation) {
                case ComparaseOperation::EQUAL:
                    return $resourceValue == $condition->value;
                case ComparaseOperation::NOT_EQUAL:
                    return $resourceValue != $condition->value;
                case ComparaseOperation::GREATER_THAN:
                    return $resourceValue > $condition->value;
                case ComparaseOperation::GREATER_THAN_OR_EQUAL:
                    return $resourceValue >= $condition->value;
                case ComparaseOperation::LESS_THAN:
                    return $resourceValue < $condition->value;
                case ComparaseOperation::LESS_THAN_OR_EQUAL:
                    return $resourceValue <= $condition->value;
                default:
                    return false;
            }
        }
        return true;
    }


    /**
     * get Current Cycle Items
     * @return \Illuminate\Support\Collection
     */
    protected function getCurrentCycleItems(): Collection
    {
        if (!is_null($this->_currentCycleItems)) {
            return $this->_currentCycleItems;
        }

        $numberOfCycle = $this->getNumberOfCycle();
        $this->_currentCycleItems = new Collection();

        foreach ($this->getPlanItems() as $temp) {
            if ($numberOfCycle >= $temp->getStartAtCycle() && $this->checkCondition($temp) && $this->isInInterval($temp)) {
                if ($temp->percent_price) {
                    if ($parent = $this->_currentCycleItems->firstWhere('id', $temp->percent_price->parent_item_id)) {
                        $temp->price = (int) round($temp->percent_price->value * $parent->price) / 100;
                        $temp->description =  "{$temp->percent_price->value}%  {$parent->name}";
                        $this->_currentCycleItems->add($temp);
                    }
                } else {
                    $this->_currentCycleItems->add($temp);
                }
            }
        }
        return $this->_currentCycleItems;
    }

    /**
     * @return array
     */
    public function getDataOfCycle()
    {
        return [
            'plan_id' => $this->plan->plan_id ?? $this->plan->getKey(),
            'number_of_cycle' => $this->getNumberOfCycle(),
            'price' => $this->getCurrentCycleItems()->sum('price'),
            'started_at' => $this->getCycleStartedAt(),
            'ended_at' => $this->getCycleEndedAt(),
            'resource_type' => $this->getResource()->getMorphClass(),
            'resource_id' => $this->getResource()->getKey(),
        ];
    }

    public function getDataOfTrialCycle(): array
    {

        if ($this->getPlanCycleHelper()->getTrialDays()) {
            return [
                'plan_id' => $this->plan->getKey(),
                'number_of_cycle' => 0,
                'price' => 0,
                'status' => 1,
                'started_at' => $this->getPlanCycleHelper()->getTrialStartAt(),
                'ended_at' => $this->getPlanCycleHelper()->getTrialEndAt(),
                'resource_type' => $this->getResource()->getMorphClass(),
                'resource_id' => $this->getResource()->getKey(),
            ];
        }
        return [];
    }

    /**
     * Summary of validate
     * @throws Exception
     * @return void
     */
    function validate()
    {
        $numberOfCycle = $this->getNumberOfCycle();
        if ($this->resource->getTotalCycle() != 0 && $this->resource->getTotalCycle() < $this->getNumberOfCycle()) {
            throw new Exception("Current Number Cycle ({$numberOfCycle}) great than Total Cycle of Resource ({$this->resource->getTotalCycle()})");
        }

        if (!$this->plan->isCycle()) {
            throw new Exception("Plan is not a Cycle (Cycle value is {$this->plan->getCycle()})");
        }

        if ($this->getNumberOfCycle() < 1) {
            throw new Exception("Number of Cycle ({$numberOfCycle}) is invalid");
        }

        if (!$this->isInInterval($this->plan)) {
            throw new Exception("Plan not in Interval. Current Number Cycle ({$numberOfCycle}) great than Total Cycle of Plan ({$this->plan->getCycle()})");
        }
    }


    /**
     * Check in Interval
     * @param CycleConfigInterface $instance
     * @return bool
     */
    protected function isInInterval(CycleConfigInterface $instance)
    {

        $intervalCount = $instance->getIntervalCount();

        $cycle = $instance->getCycle();

        $numOfInterval = $this->getNumberOfCycle();

        if ($startAtCycle = $instance->getStartAtCycle()) {
            $numOfInterval = ($numOfInterval - $startAtCycle) / $intervalCount + 1;
        }

        return $numOfInterval > 0 && is_integer($numOfInterval)
            ? ($cycle === 0 ? true : $numOfInterval <= $cycle)
            : false;
    }
}
