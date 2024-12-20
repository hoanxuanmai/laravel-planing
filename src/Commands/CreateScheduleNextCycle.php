<?php

namespace HXM\LaravelPlanning\Commands;

use Exception;
use HXM\LaravelPlanning\Actions\Creations\CreateCycleScheduleForOrder;
use HXM\LaravelPlanning\Models\PlanCycle;
use HXM\LaravelPlanning\Models\PlanOrder;
use Illuminate\Console\Command;

class CreateScheduleNextCycle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel-planning:create-plan-cycle-schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create schedule for next cycle of plan order';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = now();
        $orderModel = new PlanOrder();
        $cycleModel = new PlanCycle();
        $query = $orderModel->query()->with(['resource'])
            ->leftJoin($cycleModel->getTable(), $orderModel->getQualifiedKeyName(), $cycleModel->qualifyColumn('plan_order_id'))
            ->select([
                $orderModel->qualifyColumn('*'),
                $cycleModel->qualifyColumn('started_at'),
                $cycleModel->qualifyColumn('ended_at'),
                $cycleModel->qualifyColumn('number_of_cycle'),
                $cycleModel->qualifyColumn('status') . ' as cycle_status',
            ])
            ->whereNull($cycleModel->getQualifiedDeletedAtColumn())
            ->whereDate($cycleModel->qualifyColumn('started_at'), '<=', $now)
            ->whereDate($cycleModel->qualifyColumn('ended_at'), '>=', $now)
            ->where($cycleModel->qualifyColumn('status'), 1);

        $query->chunk(100, function ($chuck) {
            /**@var PlanOrder */
            foreach ($chuck as $order) {
                try {
                    $schedule = CreateCycleScheduleForOrder::handle($order, $order->number_of_cycle + 1);
                    if ($schedule->getKey()) {
                        $this->output->info("Order #{$order->getKey()}: created schedule for number of cycle #{$schedule->number_of_cycle}");
                    } else {
                        $this->output->warning("Order #{$order->getKey()}: can not create new schedule with number of cycle is {$schedule->number_of_cycle}");
                    }
                } catch (Exception $exception) {
                    $order->addLog($exception->getMessage());
                    $this->output->error("Order #{$order->getKey()}: {$exception->getMessage()}");
                }
            }
        });
        return 0;
    }
}
