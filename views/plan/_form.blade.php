<div class="form-group">
    <label>Name</label>
    <input type="text" class="form-control" value="{{ old('name', $plan->name) }}" name="name">
    @error('name')
        <small class="form-text text-danger">{{ $message }}</small>
    @enderror
</div>
<div class="form-group">
    <label>Description</label>
    <textarea name="description" class="form-control" rows="3">{{ old('description', $plan->description) }}</textarea>
</div>
<div class="row">
    <div class="col-12 col-sm-6">
        <div class="form-group">
            <label>Resource</label>
            <select name="resource" class="form-control">
                <option value="">Select one</option>
                @foreach (LaravelPlanning::getResources() as $resource)
                    <option value="{{ $resource }}" {{ old('resource', $plan->resource) == $resource ? 'selected' : '' }}>{{ $resource }}</option>
                @endforeach
            </select>
            @error('resource')
                <small class="form-text text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>
    <div class="col-12 col-sm-6">
        <div class="form-group">
            <label>Code</label>
            <input type="text" class="form-control" value="{{ old('code', $plan->code) }}" name="code">
            @error('code')
                <small class="form-text text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>
</div>


<div class="row">
    <div class="col-12 col-sm-4">
        <div class="form-group">
            <label>Cycle</label>
            <input type="number" class="form-control" value="{{ old('cycle', $plan->cycle) }}" name="cycle" step="1" min="0">
            @error('cycle')
                <small class="form-text text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="form-group">
            <label>Interval</label>
            <select name="interval" class="form-control">
                @foreach (\HXM\LaravelPlanning\Constants\IntervalTypes::getValues() as $des=>$value)
                    <option value="{{ $value }}" {{ old('interval', $plan->interval) == $value ? 'selected' : '' }}>{{ $des }}</option>
                @endforeach
            </select>
            @error('interval')
                <small class="form-text text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="form-group">
            <label>Interval Count</label>
            <input type="number" class="form-control" value="{{ old('interval_count', $plan->interval_count) }}" name="interval_count" step="1" min="0">
            @error('interval_count')
                <small class="form-text text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>
</div>
