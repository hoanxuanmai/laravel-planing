<?php

namespace HXM\LaravelPlanning\Actions\Creations;

use \Exception;
use \HXM\LaravelPlanning\Helpers\PlanCycleHelper;
use \Illuminate\Support\Collection;
use HXM\LaravelPlanning\Contracts\PlanOrderInstanceInterface;
use HXM\LaravelPlanning\Contracts\PlanResourceInstance;
use HXM\LaravelPlanning\Models\PlanCycle;
use HXM\LaravelPlanning\Models\PlanOrder;
use Illuminate\Database\Eloquent\Model;

class CreateNextCycleFromPlanOrderCalculator extends BaseCalculator
{
    /**
     * Undocumented variable
     *
     * @var PlanOrder
     */
    protected $planOrder;
    protected $numberOfCycle;

    /**
     * Summary of _planCycleHelper
     * @var PlanCycleHelper
     */
    protected $_planCycleHelper;

    /**
     * @var PlanCycle
     */
    protected $_currentPlanCycle;

    /**
     * Summary of _currentNumberOfCycle
     * @var int
     */
    protected $_currentNumberOfCycle;

    /**
     * Summary of __construct
     * @param PlanResourceInstance&Model $resource
     */
    function __construct(PlanResourceInstance $resource, PlanOrderInstanceInterface $planOrder)
    {
        $this->planOrder = $planOrder;
        parent::__construct($resource, $planOrder);
    }

    /**
     *
     * @return null|PlanOrder
     */
    function getPlanOrder(): ?PlanOrder
    {
        return $this->planOrder;
    }
    /**
     *
     * @return int
     */
    function getNumberOfCycle(): int
    {
        if ($this->_currentNumberOfCycle == null) {
            $this->_currentNumberOfCycle = $this->currentOrderCycle()->number_of_cycle + 1;
        }
        return $this->_currentNumberOfCycle;
    }

    /**
     * Summary of handle
     * @return PlanCycle
     * @throws Exception
     */
    function handle(int $numberOfCycle = null): PlanCycle
    {
        $this->_currentNumberOfCycle = $numberOfCycle;

        $this->validate();

        $items = $this->getCurrentCycleItems();

        $dataCycle = $this->planOrder->cycles()->make($this->getDataOfCycle());

        $dataCycle->setRelation('items', $items);
        $dataCycle->setRelation('planOrder', $this->planOrder);

        return $dataCycle;
    }

    function getPlanCycleHelper(): PlanCycleHelper
    {
        $this->_planCycleHelper == null && $this->_planCycleHelper = PlanCycleHelper::createByNumberOfCycle($this->planOrder->getInterval(), $this->planOrder->getIntervalCount(), $this->getNumberOfCycle(), $this->planOrder->started_at);
        return $this->_planCycleHelper;
    }

    protected function currentOrderCycle(): PlanCycle
    {
        if ($this->_currentPlanCycle == null) {
            $this->_currentPlanCycle = $this->getPlanOrder()->cycles()->whereStatus(1)->orderByNumberOfCycle()->first();
            if (empty($this->_currentPlanCycle)) {
                throw new \Exception('Can not find current Order Cycle');
            }
        }
        return $this->_currentPlanCycle;
    }

    function getPlanItems(): Collection
    {
        return $this->getPlanOrder()->getItems();
    }
}
