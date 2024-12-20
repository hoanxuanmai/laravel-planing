@extends('laravel_planning::layout')

@section('content')

<div class="container-fluid">
    <form action="{{ LaravelPlanning::route('update', $plan) }}" method="POST">
        @csrf
        @method('PUT')
        @include('laravel_planning::plan._form')
        <div class="form-group text-center mt-5">
            <button class="btn btn-success">Save</button>
        </div>
    </form>
</div>
@endsection

@push('scripts-bottom')

@endpush
