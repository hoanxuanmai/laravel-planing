<?php

namespace HXM\LaravelPlanning\Actions\Creations;

use \Exception;
use DateInterval;
use DateTimeInterface;
use HXM\LaravelPlanning\Helpers\PlanCycleHelper;
use HXM\LaravelPlanning\Models\PlanCycle;
use HXM\LaravelPlanning\Models\PlanOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CreatePlanOrderCalculator extends BaseCalculator
{

    /**
     * the Quote
     *
     * @var PlanOrder|null
     */
    public $quote = null;

    /**
     * list of Items
     *
     * @var Collection
     */
    protected $items = null;

    /**
     * the Ended Date
     *
     * @var Carbon|null
     */
    protected $_now = null;

    /**
     * Summary of diffTime
     * @var DateInterval|null
     */
    protected $_diffTime = null;

    /**
     * Summary of _numberOfCycle
     * @var int|null
     */
    protected $_numberOfCycle = null;

    /**
     * Summary of _planCycleHelper
     * @var PlanCycleHelper
     */
    protected $_planCycleHelper;

    /**
     * Status of action
     *
     * @var bool
     */

    protected $_processed = false;
    protected int $_trialDays = 0;

    function addTrialDays(int $days = 0)
    {
        $this->_trialDays = $days;
        return $this;
    }

    /**
     * Summary of handle
     * @param \Illuminate\Support\Carbon|null $startedAt
     * @param int $numberOfCycle
     * @throws Exception
     * @return PlanOrder
     */
    function handle(Carbon $startedAt = null, int $numberOfCycle = null, PlanOrder $initPlanOrder = null): PlanOrder
    {

        if ($this->_processed) {
            return $this->quote;
        }
        $this->_processed = true;

        $plan = $this->plan;

        $this->_planCycleHelper = $numberOfCycle == null
            ? PlanCycleHelper::createByCurrentDay($plan->getInterval(), $plan->getIntervalCount(), $startedAt ?? $this->resource->getPlanStartedAt() ?? null)
            : PlanCycleHelper::createByNumberOfCycle($plan->getInterval(), $plan->getIntervalCount(), $numberOfCycle);

        $this->_planCycleHelper->addTrialDays($this->_trialDays);

        if (!$this->isFisrt()) {
            $this->validate();
        }

        $this->quote = $initPlanOrder ?? $plan->orders()->make([
            'name' => $plan->name,
            'description' => $plan->description,
            'started_at' => $this->getPlanStartedAt(),
            'interval' => $plan->getInterval(),
            'interval_count' => $plan->getIntervalCount(),
            'total_cycle' => $this->resource->getTotalCycle()
        ]);


        $orderItems = new Collection();

        foreach ($this->plan->getItems() as $item) {

            $orderItem = $this->quote->items()->make(array_merge($item->toArray(), ['plan_item_id' => $item->getKey()]));

            if ($item->condition) {
                $orderItem->setRelation('condition', $orderItem->condition()->make($item->condition->toArray()));
            }
            if ($item->percent_price) {
                $orderItem->setRelation('percent_price', $item->percent_price);
            }
            $orderItems->push($orderItem);
        }

        $this->quote->setRelation('items', $orderItems);

        $quoteTarget = $this->quote->resource();
        $this->quote->forceFill([
            $quoteTarget->getForeignKeyName() => $this->resource->getKey(),
            $quoteTarget->getMorphType() => $this->resource->getMorphClass()
        ]);

        $this->quote->setRelation('cycles', collect());

        if ($trialData = $this->getDataOfTrialCycle()) {
            $this->quote->cycles->add($this->quote->cycles()->make($trialData));
        }

        $cycle = $this->quote->cycles()->make($this->getDataOfCycle());

        $cycle->setRelation('items', $this->getCurrentCycleItems());

        $this->quote->cycles->add($cycle);

        $this->quote->setRelation('plan', $this->plan);

        return $this->quote;
    }

    public function getCycle(): ?PlanCycle
    {
        if ($this->_processed) {
            $this->handle();
        }
        if ($quote = $this->getPlanOrder()) {
            return $quote->cycles->last();
        }
        return new PlanCycle($this->getDataOfCycle());
    }


    /**
     * Summary of getQuote
     * @return PlanOrder|null
     */

    function getPlanOrder(): ?PlanOrder
    {
        if (!$this->_processed) {
            $this->handle();
        }
        return $this->quote;
    }


    /**
     * @return bool
     */

    protected function isFisrt()
    {
        return $this->getNumberOfCycle() == 1;
    }

    function getPlanCycleHelper(): PlanCycleHelper
    {
        $this->_planCycleHelper == null && $this->_planCycleHelper = PlanCycleHelper::createByCurrentDay($this->getPlan()->getInterval(), $this->getPlan()->getIntervalCount(), $this->getResource()->getPlanStartedAt() ?? null);
        return $this->_planCycleHelper;
    }

    function getPlanItems(): Collection
    {
        return $this->getPlan()->getItems();
    }
}
