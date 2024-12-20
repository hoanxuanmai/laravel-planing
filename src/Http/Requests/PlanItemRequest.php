<?php

namespace HXM\LaravelPlanning\Http\Requests;

use HXM\LaravelPlanning\Models\Plan;
use HXM\LaravelPlanning\Models\PlanItem;
use HXM\LaravelPlanning\Constants\PriceTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PlanItemRequest extends FormRequest
{
    public ?Plan $plan = null;
    public ?PlanItem $item = null;

    public string $table = '';

    function __construct()
    {
        $this->table = (new PlanItem())->getTable();
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'name' => 'required',
            'description' => 'nullable',
            'price_type' => ['sometimes', Rule::in(PriceTypes::getValues())],
            'price' => 'required|numeric',
            'percent_price' => 'nullable|array',
            'percent_price.parent_item_id' => $this->usePercentPrice() ? [Rule::requiredIf($this->usePercentPrice()), function ($attribute, $value, $fail) {
                if (!static::getParentItemPrice($this->plan, $this->item, ['id' => $value])->count()) {
                    $fail('Parent Item is invalid!');
                }
            }] : '',
            'percent_price.value' => $this->usePercentPrice() ? 'required|numeric' : '',
            'cycle' => 'required|integer|min:0',
            'start_at_cycle' => 'required|integer|min:1',
            'interval_count' => 'required|integer|min:1',
            'condition' => 'nullable|array',
            'condition.resource' => 'required_with:condition',
            'condition.attribute' => 'required_with:condition',
            'condition.operation' => 'required_with:condition',
            'condition.value' => 'nullable',

        ];
    }

    function prepareForValidation()
    {
        $this->plan = $this->route()->parameter('plan');
        $this->item = $this->route()->parameter('item');

        if (!$this->input('condition.resource')) {
            $this->merge(['condition' => null]);
        }
        if ($this->usePercentPrice() && $this->input('percent_price.parent_item_id') && $this->input('percent_price.value')) {
            $price = optional(PlanItem::find($this->input('percent_price.parent_item_id')))->price;
            $price = round($price * (float) $this->input('percent_price.value'));
            $this->merge(['price' => $price / 100]);
        }
    }

    public function usePercentPrice(): bool
    {

        return $this->input('price_type') == PriceTypes::PERCENT || ($this->input('price_type') != PriceTypes::FIXED && $this->input('percent_price'));
    }

    /**
     * get Parent Item to calculation percent price
     * @param \App\Models\Plan\Plan $plan
     * @param \App\Models\Plan\PlanItem|null $currentItem
     * @return \Illuminate\Support\Collection
     */
    static function getParentItemPrice(Plan $plan, PlanItem $currentItem = null, $where = []): Collection
    {
        $where = array_merge($where, ['plan_id' => $plan->id]);
        $find = false;
        return PlanItem::where($where)
            ->when($currentItem && $currentItem->getKey, function ($q) use ($currentItem) {
                $q->where('sort', '<=', $currentItem->sort);
            })
            ->get()
            ->filter(function ($item) use ($currentItem, &$find) {
                $currentItem && $currentItem->id == $item->id && $find = true;
                return !$find;
            });
    }
}
