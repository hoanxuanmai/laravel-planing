<?php

namespace HXM\LaravelPlanning\Helpers;

use DateInterval;
use DateTimeInterface;
use HXM\LaravelPlanning\Constants\IntervalTypes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PlanCycleHelper
{
    /**
     * Summary of _interval
     * @var string
     */
    protected $_interval;

    /**
     * Summary of _interval_count
     * @var int
     */
    protected $_intervalCount;

    /**
     * Summary of _initStartAt
     * @var Carbon
     */
    protected $_initDate;


    /**
     * Summary of _planStartAt
     * @var Carbon
     */
    protected $_planStartAt;

    /**
     *
     * @var Carbon
     */
    protected $_cycleStartAt;

    /**
     * Summary of _cycleEndAt
     * @var Carbon
     */
    protected $_cycleEndAt;

    /**
     * Summary of _numberOfCycle
     * @var int
     */
    protected $_numberOfCycle;

    /**
     * Summary of _trialDays
     * @var int
     */
    protected int $_trialDays = 0;

    /**
     * Summary of _trialStartAt
     * @var DateTimeInterface|null
     */
    protected $_trialStartAt = null;

    /**
     * Summary of _trialEndAt
     * @var DateTimeInterface|null
     */
    protected $_trialEndAt = null;


    protected function __construct(string $interval, int $intervalCount, DateTimeInterface $initDate, int $numberOfCycle = 1)
    {
        $this->_interval = $interval;
        $this->_intervalCount = $intervalCount;
        $this->_initDate = Carbon::parse($initDate);
        $this->_numberOfCycle = $numberOfCycle;
    }

    public function interval(): string
    {
        return $this->_interval;
    }

    public function intervalCount(): int
    {
        return $this->_intervalCount;
    }

    public function planStartAt(): Carbon
    {
        $this->_planStartAt === null && $this->_planStartAt = (clone $this->_initDate)->addDays($this->_trialDays)->startOfDay();
        return $this->_planStartAt;
    }

    public function cycleStartAt(): Carbon
    {
        if ($this->_cycleStartAt == null) {
            $this->calculateCycleRangeDates();
        }
        return $this->_cycleStartAt;
    }
    public function cycleEndAt(): Carbon
    {
        if ($this->_cycleEndAt == null) {
            $this->calculateCycleRangeDates();
        }
        return $this->_cycleEndAt;
    }
    public function numberOfCycle(): int
    {
        return $this->_numberOfCycle;
    }

    public function addTrialDays(int $trialDays)
    {
        if ($this->_numberOfCycle != 1) return $this;

        $this->_trialDays = max(0, $trialDays);

        if ($this->_trialDays > 0) {
            $this->_trialStartAt = (clone $this->_initDate)->startOfDay();
            $this->_trialEndAt = (clone $this->_initDate)->addDays($this->_trialDays - 1)->endOfDay();
        } else {
            $this->_trialStartAt = null;
            $this->_trialEndAt = null;
        }
        $this->_planStartAt = null;

        $this->calculateCycleRangeDates();

        return $this;
    }

    function getTrialStartAt(): ?Carbon
    {
        return $this->_trialStartAt;
    }
    function getTrialEndAt(): ?Carbon
    {
        return $this->_trialEndAt;
    }

    function getTrialDays(): int
    {
        return $this->_trialDays;
    }


    private function calculateCycleRangeDates(): self
    {
        $method = 'add' . ucfirst($this->_interval);

        $planStartAt = $this->planStartAt();

        $this->_cycleStartAt = (clone $planStartAt)->{$method}($this->_intervalCount * ($this->_numberOfCycle - 1))->startOfDay();

        $this->_cycleEndAt = (clone $planStartAt)->{$method}($this->_intervalCount * $this->_numberOfCycle)->endOfDay()->subDay();

        return $this;
    }

    static function roundToYear(DateInterval $dateInterval): int
    {
        if ($dateInterval->m > 0 || $dateInterval->d > 0 || $dateInterval->h > 0 || $dateInterval->i > 0) {
            return $dateInterval->y + 1;
        }
        return $dateInterval->y;
    }

    static function roundToMonth(DateInterval $dateInterval): int
    {
        $month = $dateInterval->m + $dateInterval->y * 12;
        if ($dateInterval->d > 0 || $dateInterval->d > 0 || $dateInterval->h > 0 || $dateInterval->i > 0) {
            $month += 1;
        }
        return $month;
    }
    static function roundToDay(DateInterval $dateInterval): int
    {
        if ($dateInterval->h > 0 || $dateInterval->i > 0) {
            return $dateInterval->days + 1;
        }
        return $dateInterval->days;
    }

    static function getNumberOfCycleByRange(DateTimeInterface $start, DateTimeInterface $end, string $interval, int $intervalCount): int
    {
        $diffTime = $start->diff($end);
        $numberOfCycle = 0;

        if ($diffTime->invert) {

            throw new \Exception('Time Line invalid. Start Plan at: ' . (string) $start . ', Now at: ' . (string) $end);
        }

        switch ($interval) {
            case IntervalTypes::DAY:
                $numberOfCycle = self::roundToDay($diffTime) / $intervalCount;
                break;
            case IntervalTypes::WEEK:
                $numberOfCycle = self::roundToDay($diffTime) / 7 / $intervalCount;
                break;
            case IntervalTypes::MONTH:
                $numberOfCycle = self::roundToMonth($diffTime) / $intervalCount;
                break;
            case IntervalTypes::YEAR:
                $numberOfCycle = self::roundToYear($diffTime) / $intervalCount;
                break;
            default:
                throw new \InvalidArgumentException("Interval {$interval} is invalid");
        }
        return $numberOfCycle > 0 ? ceil($numberOfCycle) : $numberOfCycle;
    }



    static function createByNumberOfCycle(string $interval = 'day', int $intervalCount = 1, int $numberOfCycle = 1, DateTimeInterface $planStartAt = null): self
    {
        $planStartAt = Carbon::parse($planStartAt)->startOfDay();
        $instance = new self($interval, $intervalCount, $planStartAt, $numberOfCycle);
        return $instance;
    }
    static function createByCurrentDay(string $interval = 'day', int $intervalCount = 1, DateTimeInterface $planStartAt = null, DateTimeInterface $currentDay = null): self
    {
        $instance = new self($interval, $intervalCount, Carbon::parse($planStartAt));

        $instance->_numberOfCycle = $planStartAt == null && $currentDay == null ? 1 : self::getNumberOfCycleByRange($instance->planStartAt(), Carbon::parse($currentDay), $instance->interval(), $instance->intervalCount());

        return $instance;
    }
}
