<div class="col-2">
    <div class="form-group">
        <label>{{ $attribute }}</label>
        <select name="resource[{{ $attribute }}]" class="form-control">
            <option value="">Select Value</option>
            @foreach ($cast->getValues() as $des => $vl)
                <option value="{{ $vl }}" {{ data_get($attributes, $attribute) == $vl ? 'selected' : '' }}>({{ $vl }}) {{ $des }}</option>
            @endforeach
        </select>
    </div>
</div>
