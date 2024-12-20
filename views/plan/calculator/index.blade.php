<div class="row">
    @php
        $attributes = $resource->getAttributes();
        $casts = $resource->getCasts();
    @endphp
    @foreach ($resource->getFillable() as $attribute)
        @php

            $cast = $casts[$attribute] ?? null;
            $cast && class_exists($cast) && $cast = (new $cast);

        @endphp
        @if ($cast && $cast instanceof \HXM\LaravelPlanning\Contracts\EnumInterface)
            @include('laravel_planning::plan.calculator.enum_input', ['cast' => $cast])
        @else
            @include('laravel_planning::plan.calculator.base_input')
        @endif
    @endforeach

</div>
