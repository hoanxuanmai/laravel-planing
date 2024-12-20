@extends('laravel_planning::layout')

@section('content')

<div class="container-fluid">
    <form action="{{ LaravelPlanning::route('store') }}" method="POST">
        @csrf
        @include('laravel_planning::plan._form')

        <div class="form-group text-center">
            <button class="btn btn-success">Create</button>
        </div>
    </form>
</div>
@endsection

@push('scripts-bottom')

@endpush
