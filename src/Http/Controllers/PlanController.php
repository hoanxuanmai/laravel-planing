<?php

namespace HXM\LaravelPlanning\Http\Controllers;

use Exception;
use HXM\LaravelPlanning\Actions\Creations\CreatePlanOrderCalculator;
use HXM\LaravelPlanning\Facades\LaravelPlanning;
use HXM\LaravelPlanning\Http\Requests\PlanRequest;
use HXM\LaravelPlanning\Models\Plan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Yajra\DataTables\Facades\DataTables;

class PlanController extends BaseController
{
    function index()
    {
        return $this->view('plan.index');
    }

    function create(Plan $plan)
    {
        $this->addBreadcrumb('Create Plan');
        return $this->view("plan.create", compact('plan'));
    }

    function store(PlanRequest $request)
    {

        $plan = Plan::create($request->validated());

        return redirect()->to(LaravelPlanning::route('index'))->with('success', 'Created');
    }

    function show(Plan $plan)
    {

        $this->addBreadcrumb($plan->name);

        return $this->view("plan.show", compact('plan'));
    }

    function edit(Plan $plan)
    {
        $this->addBreadcrumb($plan->name);
        return $this->view("plan.edit", compact('plan'));
    }

    function update(Plan $plan, PlanRequest $planRequest)
    {

        $plan->update($planRequest->validated());

        return redirect()->to(LaravelPlanning::route('index'))->with('success', 'Updated');
    }

    function calculator(Plan $plan)
    {
        $this->addBreadcrumb($plan->name);
        $start = request('start') ? Date::parse(request('start')) : null;
        $error = null;
        $calculator = null;

        try {
            /**
             * @var Model $resource
             */
            $resource = app()->make($plan->resource);
            $id = request('resource_id');
            if ($id) {
                $resource = $resource->find($id) ?: $resource;
                is_null($start) && $start = Date::parse($resource->start_date);
            }

            $resource->forceFill(request('resource', []));

            $calculator = new CreatePlanOrderCalculator($resource, $plan);

            $planOrder = $calculator->handle($start, request('number_of_period', null));
            // $planOrder->save();
        } catch (Exception $e) {
            $error = $e->getMessage();
            // $calculator = null;
        }


        return $this->view("plan.calculator", compact('plan', 'calculator'))->withErrors(compact('error'));
    }

    function datatable()
    {
        $query = Plan::query()->withCount('orders');
        $filterStatus = request('status');

        if ($filterStatus == 'all') {
            $query->withTrashed();
        } elseif ($filterStatus == 'trashed') {
            $query->onlyTrashed();
        }

        return DataTables::of($query)
            ->addColumn('countOrdersHtml', function (Plan $plan) {
                return '<a class="" title="List Order" href="' . LaravelPlanning::route('orders.index', $plan) . '">' . $plan->orders_count . '</a>';
            })
            ->addColumn('actionHtml', function (Plan $plan) {
                return $plan->trashed() ? '' : ''
                    . '<a class="btn btn-sm btn-icon" title="Calculator" href="' . LaravelPlanning::route('calculator', $plan) . '"><i class="fas fa-calculator"></i></a>'
                    . '<a class="btn btn-sm btn-icon" title="Item List" href="' . LaravelPlanning::route('show', $plan) . '"><i class="fas fa-list"></i></a>'
                    . '<a class="btn btn-sm btn-icon" title="Order List" href="' . LaravelPlanning::route('orders.index', $plan) . '"><i class="fas fa-cart-shopping"></i></a>'
                    . '<a class="btn btn-sm btn-icon" title="Edit" href="' . LaravelPlanning::route('edit', $plan) . '"><i class="fas fa-edit"></i></a>';
            })
            ->rawColumns(['actionHtml', 'countOrdersHtml'])
            ->make('true');
    }
}
