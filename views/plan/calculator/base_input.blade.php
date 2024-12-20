<div class="col-2">
    <div class="form-group">
        <label>{{ $attribute }}</label>
        <input type="text" class="form-control" value="{{ data_get($attributes, $attribute) }}" name="resource[{{ $attribute }}]">
    </div>
</div>
