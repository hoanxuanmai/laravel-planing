@extends('laravel_planning::layout')

@section('content')
@php
    $review = optional($calculator)->getPlanOrder();
    $request = collect(request('resource', []))->filter();
@endphp

<div class="container-fluid">
    <div class="card mb-5 pb-5">
        <form class="card-header" action="{{ LaravelPlanning::route('calculator', $plan) }}" method="POST">
            @csrf
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label>Resource Id</label>
                        <input type="number" class="form-control" value="{{ request('resource_id', '') }}" name="resource_id">
                        @error('start')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label>Start</label>
                        <input type="date" class="form-control" value="{{ request('start', ($review ? $review->started_at->format('Y-m-d') : null)) }}" name="start">
                        @error('start')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label>Number Of Period</label>
                        <input type="number" class="form-control" value="{{ request('number_of_period', optional($calculator)->getNumberOfCycle()) }}" name="number_of_period" min="1" step="1">
                        @error('number_of_period')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>
            @if ($calculator)
            <div class="panel-group" id="accordion">
                <div class="panel panel-default">
                  <div class="panel-heading">
                    <h4 class="panel-title">
                      <a data-bs-toggle="collapse" class="{{ $request->count() ? 'collapsed' : '' }}" data-parent="#accordion" href="#collapse1">{{ $calculator->getPlan()->resource }}</a>
                    </h4>
                  </div>
                  <div id="collapse1" class="panel-collapse in collapse {{ $request->count() ? 'show' : '' }}">
                    <div class="panel-body">
                        @include('laravel_planning::plan.calculator.index', ['resource' => $calculator->getResource()])
                    </div>
                  </div>
                </div>
            </div>
            @endif
            <div class="text-center">
                <button class="btn btn-success">Review</button>
            </div>
        </form>
    </div>

    @if ($review)
        @php
            $history = $review->cycles->first();
        @endphp
        <div class="card">
            <div class="card-header">
                <div class="row mt-5">
                    <div class="col-2">
                        <label>Plan:</label>
                    </div>
                    <div class="col-10">
                        {{ $review->name }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-2">
                        <label>Period:</label>
                    </div>
                    <div class="col-10">
                        <strong>From</strong> {{ $history->started_at->format('d/m/Y H:i:s') }} <strong>To</strong> {{ $history->ended_at->format('d/m/Y H:i:s') }}
                    </div>
                </div>
            </div>
            <div class="card-content">

                <table class="table table-striped table-bordered dataTable no-footer">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($history->items as $index => $item)
                            <tr>
                                <td><a href="{{ route('plans.items.edit', [$review->plan, $item]) }}" target="_blank">{{ $index + 1 }}</a></th>
                                <td>
                                    {{ $item->name }}
                                    @if($item->description)
                                    <br>{{ $item->description }}
                                    @endif
                                </th>
                                <td>{{ $item->price }} USD</th>
                            </tr>
                        @endforeach

                    </tbody>
                    <tfoot>
                        <tr>
                            <th></th>
                            <th class="text-right">Total: </th>
                            <th>{{ $history->price }} USD</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>


    @else
        @error('error')
            <div class="alert alert-error text-danger">
                {{ $message }}
            </div>
        @enderror
    @endif

</div>
@endsection
