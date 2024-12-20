<?php

namespace HXM\LaravelPlanning;

use HXM\LaravelPlanning\Commands\CreateScheduleNextCycle;
use HXM\LaravelPlanning\Commands\RunScheduleCycle;
use HXM\LaravelPlanning\Facades\LaravelPlanning;
use HXM\LaravelPlanning\Http\Controllers;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class LaravelPlanningProvider extends ServiceProvider
{
    function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel_planning.php', 'laravel_planning');

        $this->app->singleton(LaravelPlanningManager::class, function ($app) {
            return new LaravelPlanningManager($app['config']->get('laravel_planning'));
        });
        if (!class_exists('LaravelPlanning')) {
            class_alias(LaravelPlanning::class, 'LaravelPlanning');
        }

    }

    function boot()
    {
        if ($this->app->runningInConsole()) {
            config('laravel_planning.useMigration', false) && $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations')
            ], 'laravel-planning:migration');

            $this->publishes([__DIR__ . '/../config/laravel_planning.php' => config_path('laravel_planning.php')
            ], 'laravel-planning:config');

            $this->commands([
                CreateScheduleNextCycle::class,
                RunScheduleCycle::class
            ]);

            // Run Schedule
            config('laravel_planning.schedule', true) && $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
                $schedule->command('laravel-planning:run-cycle-schedules')->cron(config('laravel_planning.cron', '10 0 * * *'));
            });
        }

        foreach (config('laravel_planning.listeners') as $event => $listeners) {
            foreach (Arr::wrap($listeners) as $listener) {
                Event::listen($event, $listener);
            }
        }
        if (config('laravel_planning.pannel.enable', true)) {
            $this->loadViewsFrom(__DIR__ . '/../views', 'laravel_planning');
            $this->registerRouter();
        }
    }

    protected function registerRouter()
    {
        Route::group([
            'prefix' => config('laravel_planning.pannel.prefix', 'plans'),
            'middleware' => config('laravel_planning.pannel.middleware', []),
            'as' => config('laravel_planning.pannel.as', 'plans.'),
        ], function () {
            Route::get('/', [Controllers\PlanController::class, 'index'])->name('index');
            Route::post('/', [Controllers\PlanController::class, 'store'])->name('store');
            Route::get('/create', [Controllers\PlanController::class, 'create'])->name('create');
            Route::get('/datatable', [Controllers\PlanController::class, 'datatable'])->name('datatable');
            Route::any('/{plan}/calculator', [Controllers\PlanController::class, 'calculator'])->name('calculator');
            Route::get('/{plan}/edit', [Controllers\PlanController::class, 'edit'])->name('edit');
            Route::get('/{plan}/show', [Controllers\PlanController::class, 'show'])->name('show');
            Route::put('/{plan}', [Controllers\PlanController::class, 'update'])->name('update');
            Route::delete('/{plan}', [Controllers\PlanController::class, 'destroy'])->name('destroy');


            Route::group([
                'as' => 'items.',
                'prefix' => '/{plan}/items'
            ], function () {
                Route::get('/', [Controllers\PlanItemController::class, 'index'])->name('index');
                Route::post('/', [Controllers\PlanItemController::class, 'store'])->name('store');
                Route::get('/create', [Controllers\PlanItemController::class, 'create'])->name('create');
                Route::get('/datatable', [Controllers\PlanItemController::class, 'datatable'])->name('datatable');
                Route::get('/{item}/edit', [Controllers\PlanItemController::class, 'edit'])->name('edit');
                Route::put('/{item}', [Controllers\PlanItemController::class, 'update'])->name('update');
                Route::delete('/{item}', [Controllers\PlanItemController::class, 'destroy'])->name('destroy');
            });
            Route::group([
                'as' => 'orders.',
                'prefix' => '/{plan}/orders'
            ], function () {
                Route::get('/', [Controllers\PlanOrderController::class, 'index'])->name('index');
                Route::any('/datatable', [Controllers\PlanOrderController::class, 'datatable'])->name('datatable');

                Route::any('/{planOrder}/show', [Controllers\PlanOrderController::class, 'show'])->name('show');
            });
            Route::get('/plan-orders/{planOrder}/cycles/datatable', [Controllers\PlanOrderController::class, 'cycleDatatable'])->name('orders.cycles.datatable');
            Route::get('/plan-orders/{planOrder}/logs/datatable', [Controllers\PlanOrderController::class, 'logDatatable'])->name('orders.logs.datatable');
            Route::get('/plan-orders/{planOrder}/schedules/datatable', [Controllers\PlanOrderController::class, 'scheduleDatatable'])->name('orders.schedules.datatable');
        });
    }
}
