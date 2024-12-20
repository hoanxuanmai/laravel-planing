<?php

namespace HXM\LaravelPlanning\Http\Requests;

use HXM\LaravelPlanning\Models\Plan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PlanRequest extends FormRequest
{
    public ?Plan $plan = null;

    public string $table = '';

    function __construct()
    {
        $this->table = (new Plan())->getTable();
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
            'resource' => 'required',
            'code'  => [
                'required',
                Rule::unique($this->table, 'code')->ignore($this->plan)
            ],
            'cycle' => 'required|integer|min:0',
            'interval' => 'required|in:day,week,month,year',
            'interval_count' => 'required|integer|min:1'

        ];
    }

    function prepareForValidation()
    {
        $this->plan = $this->route()->parameter('plan');
    }
}
