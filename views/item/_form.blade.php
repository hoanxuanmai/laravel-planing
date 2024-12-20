
@php
    $priceType = \HXM\LaravelPlanning\Constants\PriceTypes::FIXED;
    if ($item->percent_price) {
        $priceType = \HXM\LaravelPlanning\Constants\PriceTypes::PERCENT;
    }
@endphp

<div class="form-group">
    <label>Name</label>
    <input type="text" class="form-control" value="{{ old('name', $item->name) }}" name="name">
    @error('name')
        <small class="form-text text-danger">{{ $message }}</small>
    @enderror
</div>
<div class="form-group">
    <label>Description</label>
    <textarea name="description" class="form-control" rows="3">{{ old('description', $item->description) }}</textarea>
</div>
<div class="row">
    <div class="col">
        <div class="form-group">
            <label>Price Type</label>
            <select name="price_type" class="form-control">
                @foreach (\HXM\LaravelPlanning\Constants\PriceTypes::getValues() as $des => $value)
                    <option value="{{ $value }}" {{ old('price_type', $priceType) == $value ? 'selected' : '' }}>{{ $des }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col input_price price_fixed d-none">
        <div class="form-group">
            <label>Price</label>
            <input type="number" class="form-control" value="{{ old('price', $item->price) }}" name="price">
            @error('price')
                <small class="form-text text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>
    <div class="col input_price price_percent d-none">
        <div class="form-group">
            <label>Parent Item</label>
            <select name="percent_price[parent_item_id]" class="form-control">
                @foreach ($parentPrices as $parent)
                <option value="{{ $parent->id }}" {{ old('percent_price.value', optional($item->percent_price)->parent_item_id) == $parent->id ? 'selected' : '' }}>{{ $parent->name }}({{ $parent->price }})</option>
                @endforeach

            </select>
            @error('percent_price.parent_item_id')
                <small class="form-text text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>
    <div class="col input_price price_percent d-none">
        <div class="form-group">
            <label>Value %</label>
            <input type="number" class="form-control" value="{{ old('percent_price.value', optional($item->percent_price)->value) }}" name="percent_price[value]">
            @error('percent_price.value')
                <small class="form-text text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-sm-4">
        <div class="form-group">
            <label>Cycle</label>
            <input type="number" class="form-control" value="{{ old('cycle', $item->cycle) }}" name="cycle" step="1" min="0">
            @error('cycle')
                <small class="form-text text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="form-group">
            <label>Start At Period</label>
            <input type="number" class="form-control" value="{{ old('start_at_cycle', $item->start_at_cycle) }}" name="start_at_cycle" step="1" min="1">
            @error('start_at_cycle')
                <small class="form-text text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="form-group">
            <label>Interval Count</label>
            <input type="number" class="form-control" value="{{ old('interval_count', $item->interval_count) }}" name="interval_count" step="1" min="0">
            @error('interval_count')
                <small class="form-text text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>
</div>
<fieldset>
    <legend>Conditions</legend>
    <div class="row">
        <div class="col-3">
            <div class="form-group">
                <label>Resource</label>
                <select name="condition[resource]" class="form-control">
                    <option value="">Select Option</option>
                    <option value="{{ $plan->resource }}" {{ old('condition.resource', optional($item->condition)->resource) == $plan->resource ? 'selected' : '' }}>{{ $plan->resource }}</option>
                </select>
                @error('condition.resource')
                    <small class="form-text text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>
        @php
            $resource = (new $plan->resource);
        @endphp
        <div class="col-3">
            <div class="form-group">
                <label>Attribute</label>
                <select name="condition[attribute]" class="form-control">
                    <option value="">Select one</option>
                    @foreach ($resource->getFillable() as $attribute)
                        <option value="{{ $attribute }}" {{ old('condition.attribute', optional($item->condition)->attribute) == $attribute ? 'selected' : '' }}>{{ $attribute }}</option>
                    @endforeach
                </select>
                @error('condition.attribute')
                    <small class="form-text text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label>Operation</label>
                <select name="condition[operation]" class="form-control">
                    <option value="">Select one</option>
                    @foreach (\HXM\LaravelPlanning\Constants\ComparaseOperation::getValues() as $des => $operation)
                        <option value="{{ $operation }}" {{ old('condition.operation', optional($item->condition)->operation) == $operation ? 'selected' : '' }}>{{ $des }}</option>
                    @endforeach
                </select>
                @error('condition.operation')
                    <small class="form-text text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label>Value</label>
                <input type="text" class="form-control" value="{{ old('condition.value', optional($item->condition)->value) }}" name="condition[value]">
                @error('condition.value')
                    <small class="form-text text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>
    </div>
</fieldset>

@push('scripts')
    <script>
        $(function(){
            function changeVl(vl) {
                if (vl) {
                    $(`form .input_price:not(.price_${vl})`).addClass('d-none')
                    $(`form .input_price.price_${vl}`).removeClass('d-none')
                }
            }
            const $selector = $('form [name=price_type]');
            changeVl(String($selector.val()).toLowerCase());

            $selector.on('change', function(){
                changeVl(String($selector.val()).toLowerCase());
            })
        })
    </script>
@endpush
