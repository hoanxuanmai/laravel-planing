@extends('laravel_planning::layout')

@section('content')
    <div class="card">
        <div class="card-header border-bottom">
            <h5>{{ $plan->name }}</h5>
        </div>
        <div class="card-datatable table-responsive">
            <table class="table table-striped table-bordered" id="plan-items-table" style="width:100%">
                <thead>
                    <tr>
                        <th title="#">#</th>
                        <th title="Name">Name</th>
                        <th title="Price">Price</th>
                        <th title="Cycle">Cycle</th>
                        <th title="Start At Cycle">Start At Cycle</th>
                        <th title="Interval Count">Interval Count</th>
                        <th title="Action">Action</th>
                    </tr>
                </thead>
            </table>

        </div>
    </div>
    <div style="display:none;">
        <div id="append_elements" class="row">
            <div class="col-auto">
                <button class="btn btn-info btn-sm"><a href="{{ LaravelPlanning::route('items.create', $plan) }}"><i
                            class="fa fa-plus"></i></a></button>
                <button class="btn btn-success btn-sm"><a href="{{ LaravelPlanning::route('calculator', $plan) }}"><i
                            class="fa fa-calculator"></i></a></button>
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
            window.LaravelDataTables["plan-items-table"] = $("#plan-items-table").DataTable({
                "serverSide": true,
                "processing": true,
                "ajax": {
                    "url": "{{ LaravelPlanning::route('items.datatable', $plan) }}",
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
                    "name": "price",
                    "data": "price",
                    "title": "Price",
                    "orderable": false,
                    "searchable": false
                }, {
                    "name": "cycle",
                    "data": "cycle",
                    "title": "Cycle",
                    "orderable": false,
                    "searchable": false
                }, {
                    "name": "start_at_cycle",
                    "data": "start_at_cycle",
                    "title": "Start At Cycle",
                    "orderable": false,
                    "searchable": false
                }, {
                    "name": "interval_count",
                    "data": "interval_count",
                    "title": "Interval Count",
                    "orderable": false,
                    "searchable": false
                }, {
                    "name": "actionHtml",
                    "data": "actionHtml",
                    "title": "Action",
                    "orderable": false,
                    "searchable": false
                }],
                "responsive": true,
                "dom": "<\"row\"<\"col-md-auto\"l><\"col-md-auto\"f><\"col-md append_elements\">rt<\"col-md-6\"i><\"col-md-6\"p>>"
            });
        });
    </script>
    <script>
        function deleteItem(event, id) {
            event.preventDefault();
            const url = "{{ route('plans.items.destroy', ['plan' => $plan, 'item' => '--id--']) }}";
            if (confirm('Are you sure?')) {
                $.ajax({
                        method: 'POST',
                        url: url.replace('--id--', id),
                        data: {
                            _method: 'DELETE'
                        }
                    })
                    .always(() => {
                        LaravelDataTables['plan-items-table'].ajax.reload()
                    })
            }
        }
    </script>
    <script type="module">
        $(function() {
            $('.append_elements').append($('#append_elements'));
            $('.select_filter_status').on('change', () => LaravelDataTables['plan-items-table'].ajax.reload())
        });
    </script>
@endpush
