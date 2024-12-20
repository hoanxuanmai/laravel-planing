<?php

namespace HXM\LaravelPlanning\Http\Controllers;

use HXM\LaravelPlanning\Facades\LaravelPlanning;
use HXM\LaravelPlanning\Models\Plan;
use HXM\LaravelPlanning\Models\PlanCycle;
use HXM\LaravelPlanning\Models\PlanOrder;
use HXM\LaravelPlanning\Models\PlanOrderLog;
use Yajra\DataTables\Facades\DataTables;

class PlanOrderController extends BaseController
{
    function __construct()
    {
        $this->addBreadcrumb('Plans', LaravelPlanning::route('index'));
    }
    function index(Plan $plan)
    {
        $this->addBreadcrumb($plan->name);
        $this->addBreadcrumb('Orders List');
        return $this->view('order.index', compact('plan'));
    }

    function show(Plan $plan, PlanOrder $planOrder)
    {
        $this->addBreadcrumb($plan->name, LaravelPlanning::route('show', $plan));
        $this->addBreadcrumb('Order List', LaravelPlanning::route('orders.index', $plan));
        $this->addBreadcrumb('Order #' . $planOrder->getKey());
        return $this->view('order.show', compact('plan', 'planOrder'));
    }

    function datatable(Plan $plan)
    {
        $query = $plan->orders()->getQuery();
        return DataTables::of($query)
            ->addColumn('targetHtml', function (PlanOrder $planOrder) use ($plan) {
                return $planOrder->target_type;
            })
            ->addColumn('actionHtml', function (PlanOrder $planOrder) use ($plan) {
                return ''
                    . '<a class="btn btn-sm btn-info" title="View Order" href="' . LaravelPlanning::route('orders.show', ['plan' => $plan, 'planOrder' => $planOrder]) . '"><i class="fas fa-eye"></i></a>';
            })
            ->rawColumns(['actionHtml', 'targetHtml'])
            ->make('true');
    }

    function cycleDatatable(PlanOrder $planOrder)
    {
        $query = $planOrder->cycles()->getQuery();
        return DataTables::of($query)
            ->addColumn('targetHtml', function (PlanCycle $planCycle) {

                return $planCycle->referable_type;
            })
            ->rawColumns(['actionHtml', 'targetHtml'])
            ->make('true');
    }

    function logDatatable(PlanOrder $planOrder)
    {
        $query = $planOrder->logs()->getQuery();
        return DataTables::of($query)
            ->addColumn('targetHtml', function (PlanOrderLog $log) {
                if ($log->referable_type) {
                    return "{$log->referable_type}#{$log->referable_id}";
                }
                return '';
            })
            ->rawColumns(['targetHtml'])
            ->make('true');
    }

    function scheduleDatatable(PlanOrder $planOrder)
    {
        $query = $planOrder->schedules()->getQuery();
        return DataTables::of($query)
            ->make('true');
    }
}
