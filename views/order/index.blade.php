@extends('laravel_planning::layout')

@section('content')

    <div class="card">
        <div class="card-header">
            <h1>{{ $plan->name }}</h1>
            <div class="row">
                <div class="col-auto">
                    <label><strong>Code: </strong></label>
                    <span>{{ $plan->code }}</span>
                </div>
                <div class="col-auto">
                    <label><strong>Cycle: </strong></label>
                    <span>{{ $plan->cycle }}</span>
                </div>
                <div class="col-auto">
                    <label><strong>Interval: </strong></label>
                    <span>{{ $plan->interval }}</span>
                </div>

                <div class="col-auto">
                    <label><strong>Interval Count: </strong></label>
                    <span>{{ $plan->interval_count }}</span>
                </div>
                @if ($plan->description)
                    <div class="col-12">
                        <label><strong>Description: </strong></label>
                        <p>{{ $plan->description }}</p>
                    </div>
                @endif
            </div>
        </div>
        <div class="card-content card-datatable">

            <table class="table table-striped table-bordered" id="plan-orders-table" style="width:100%">
                <thead>
                    <tr>
                        <th title="#">#</th>
                        <th title="Name">Name</th>
                        <th title="Target">Target</th>
                        <th title="Started At">Started At</th>
                        <th title="Total Cycle">Total Cycle</th>
                        <th title="Action">Action</th>
                    </tr>
                </thead>
            </table>

        </div>
    </div>
    <div style="display:none;">
        <div id="append_elements" class="row">
            <div class="col-auto">
                <button class="btn btn-info btn-sm"><a href="{{ LaravelPlanning::route('create') }}">Add</a></button>
            </div>
            <div class="col-auto">
                <select class="select_filter_status custom-select custom-select-sm form-control form-control-sm">
                    <option value="all">All</option>
                    <option selected>Active</option>
                    <option value="trashed">Trashed</option>
                </select>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script type="text/javascript">
        $(function() {
            window.LaravelDataTables = window.LaravelDataTables || {};
            window.LaravelDataTables["plan-orders-table"] = $("#plan-orders-table").DataTable({
                "serverSide": true,
                "processing": true,
                "ajax": {
                    "url": "{{ LaravelPlanning::route('orders.datatable', ['plan' => $plan]) }}",
                    "data": function(data) {
                        data.status = $('.select_filter_status').val()
                    }
                },
                "columns": [{
                    "name": "id",
                    "data": "id",
                    "title": "#",
                    "orderable": true,
                    "searchable": true
                }, {
                    "name": "name",
                    "data": "name",
                    "title": "Name",
                    "orderable": true,
                    "searchable": true
                }, {
                    "name": "targetHtml",
                    "data": "targetHtml",
                    "title": "Target",
                    "orderable": false,
                    "searchable": false
                }, {
                    "name": "started_at",
                    "data": "started_at",
                    "title": "Started At",
                    "orderable": false,
                    "searchable": false
                }, {
                    "name": "total_cycle",
                    "data": "total_cycle",
                    "title": "Total Cycle",
                    "orderable": false,
                    "searchable": false
                }, {
                    "name": "actionHtml",
                    "data": "actionHtml",
                    "title": "Action",
                    "orderable": false,
                    "searchable": false
                }],
                "order": [
                    [0, "desc"]
                ],
                "responsive": true,
                "dom": "<\"row\"<\"col-md-auto\"l><\"col-md-auto\"f><\"col-md append_elements\">rt<\"col-md-6\"i><\"col-md-6\"p>>"
            });
        });
    </script>

    <script>
        $(function() {
            $('.append_elements').append($('#append_elements'));
            $('.select_filter_status').on('change', () => LaravelDataTables['plan-orders-table'].ajax.reload())
        });
    </script>
@endpush
