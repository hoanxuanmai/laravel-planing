@extends('laravel_planning::layout')

@section('content')

<div class="container-fluid">
    <form action="{{ LaravelPlanning::('items.store', $plan) }}" method="POST">
        @csrf
        @include('laravel_planning::item._form')

        <div class="form-group text-center">
            <button class="btn btn-success">Create</button>
        </div>
    </form>
</div>
@endsection

@push('scripts-bottom')

@endpush
