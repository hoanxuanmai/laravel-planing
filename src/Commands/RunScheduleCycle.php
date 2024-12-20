<?php

namespace HXM\LaravelPlanning\Commands;

use Exception;
use HXM\LaravelPlanning\Actions\Creations\CreateNextCycleFromPlanOrderCalculator;
use HXM\LaravelPlanning\Events\NextPlanCycleCreatedEvent;
use HXM\LaravelPlanning\Models\PlanCycleSchedule;
use Illuminate\Console\Command;

class RunScheduleCycle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel-planning:run-cycle-schedules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create next cycle of plan order';

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
        $query = PlanCycleSchedule::with(['planOrder', 'resource'])->where(['status' => 0])->whereDate('run_at', '<', $now);
        $query->chunk(100, function ($chuck) {
            foreach ($chuck as $schedule) {
                try {
                    $action = new CreateNextCycleFromPlanOrderCalculator($schedule->resource, $schedule->planOrder);
                    $cycle = $action->handle($schedule->number_of_cycle);

                    if ($cycle->save()) {

                        $schedule->update([
                            'message' => "Order #{$cycle->plan_order_id}: created cycle for number of cycle #{$cycle->number_of_cycle}",
                            'status' => 1
                        ]);

                        event(new NextPlanCycleCreatedEvent($cycle));
                    } else {
                        $schedule->update([
                            'message' => "Order #{$cycle->plan_order_id}: cannot creat cycle for number of cycle #{$cycle->number_of_cycle}",
                            'status' => 2
                        ]);
                    }
                } catch (Exception $exception) {
                    $schedule->update([
                        'message' => "Order #{$cycle->plan_order_id}: {$exception->getMessage()}",
                        'status' => 3
                    ]);
                }
            }
        });
        return 0;
    }
}
