<?php

namespace HXM\LaravelPlanning\Http\Controllers;

use HXM\LaravelPlanning\Facades\LaravelPlanning;
use HXM\LaravelPlanning\Http\Requests\PlanItemRequest;
use HXM\LaravelPlanning\Models\Plan;
use HXM\LaravelPlanning\Models\PlanItem;
use Yajra\DataTables\Facades\DataTables;

class PlanItemController extends BaseController
{
    function index()
    {
        return $this->view("item.index");
    }

    function create(Plan $plan, PlanItem $item)
    {
        $this->addBreadcrumb($plan->name, route('plans.show', $plan));
        $this->addBreadcrumb('Create Item');
        $parentPrices = PlanItemRequest::getParentItemPrice($plan, $item);

        return $this->view("item.create", compact('plan', 'item', 'parentPrices'));
    }

    function store(Plan $plan, PlanItemRequest $request)
    {
        $validated = $request->validated();

        $item = $plan->items()->create($validated);

        if ($validated['condition'] ?? null) {
            $item->condition()->create($validated['condition']);
        }
        if ($request->usePercentPrice()) {
            $item->percent_price()->create($request->input('percent_price'));
        }

        return redirect()->to(LaravelPlanning::route('show', $plan))->with('success', 'Created');
    }


    function edit(Plan $plan, PlanItem $item)
    {
        $this->addBreadcrumb($plan->name, route('plans.show', $plan));
        $this->addBreadcrumb($item->name);
        $parentPrices = PlanItemRequest::getParentItemPrice($plan, $item);
        return $this->view("item.edit", compact('plan', 'item', 'parentPrices'));
    }

    function update(Plan $plan, PlanItem $item, PlanItemRequest $planItemRequest)
    {
        $validated = $planItemRequest->validated();
        $item->update($validated);

        if ($validated['condition'] ?? null) {
            $condition = $item->condition;
            $condition ? $condition->update($validated['condition']) : $item->condition()->create($validated['condition']);
        } else {
            $item->condition()->delete();
        }

        if ($planItemRequest->usePercentPrice()) {
            $item->percent_price()->updateOrCreate([
                'plan_item_id' => $item->id
            ], $planItemRequest->input('percent_price'));
        } else {
            $item->percent_price()->delete();
        }

        return redirect()->to(LaravelPlanning::route('show', $plan))->with('success', 'Updated');
    }

    function destroy(Plan $plan, PlanItem $item)
    {
        $plan->items()->whereKey($item->getKey())->delete();
        return $item;
    }

    function datatable(Plan $plan)
    {
        $query = $plan->items();
        $filterStatus = request('status');

        if ($filterStatus == 'all') {
            $query->withTrashed();
        } elseif ($filterStatus == 'trashed') {
            $query->onlyTrashed();
        }

        return DataTables::of($query)
            ->addColumn('actionHtml', function (PlanItem $item) use ($plan) {
                return $plan->trashed() ? '' : '<a class="btn btn-sm btn-primary"
                       href="' . LaravelPlanning::route('items.edit', ['plan' => $plan, 'item' => $item]) . '"><i class="fas fa-edit"></i></a>
                    <a class="btn btn-sm btn-danger" onclick="deleteItem(event, ' . $item->id . ')"><i class="fas fa-trash"></i></a>';
            })
            ->editColumn('price', function (PlanItem $item) {
                return $item->percent_price ? "{$item->percent_price->value} %" : $item->price;
            })
            ->rawColumns(['actionHtml'])
            ->make('true');
    }
}
