@extends('laravel_planning::layout')

@section('content')
    <div class="card mt-5">
        <div class="card-header border-bottom">
            <h5>{{ $planOrder->name }}</h5>
            <div class="row">
                <div class="col-auto">
                    <label><strong>Resource: </strong></label>
                    <span>{{ $planOrder->resource->name }}
                        ({{ $planOrder->resource_type . '#' . $planOrder->resource_id }})</span>
                </div>
                <div class="col-auto">
                    <label><strong>Started At: </strong></label>
                    <span>{{ $planOrder->started_at }}</span>
                </div>
                <div class="col-auto">
                    <label><strong>Total Cycle: </strong></label>
                    <span>{{ $planOrder->total_cycle }}</span>
                </div>
                <div class="col-auto">
                    <label><strong>Interval: </strong></label>
                    <span>{{ $planOrder->interval }}</span>
                </div>

                <div class="col-auto">
                    <label><strong>Interval Count: </strong></label>
                    <span>{{ $planOrder->interval_count }}</span>
                </div>
                @if ($plan->description)
                    <div class="col-12">
                        <label><strong>Description: </strong></label>
                        <p>{{ $planOrder->description }}</p>
                    </div>
                @endif
            </div>
        </div>
        <div class="card-datatable">

            <table class="table table-striped table-bordered" id="plan-order-cycles-table" style="width:100%">
                <thead>
                    <tr>
                        <th title="#">#</th>
                        <th title="Started at">Started at</th>
                        <th title="Ended at">Ended at</th>
                        <th title="Number Of Cycle">Number Of Cycle</th>
                        <th title="Price">Price</th>
                        <th title="Target">Target</th>
                        <th title="Status">Status</th>
                    </tr>
                </thead>
            </table>

        </div>
    </div>
    <div class="card mt-5 card-action mb-12">
        <div class="card-header d-flex">
            <h3 class="card-action-title mb-0 flex-grow-1">Logs</h3>
            <div class="card-action-element">
                <ul class="list-inline mb-0">
                    <li class="list-inline-item">
                        <a href="javascript:void(0);" class="card-collapsible"><i class="fa fa-caret-down"></i></a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="card-datatable collapse" data-datatable="plan-order-logs-table">
            <table class="table table-striped table-bordered" id="plan-order-logs-table" style="width:100%">
                <thead>
                    <tr>
                        <th title="Time">Time</th>
                        <th title="Content">Content</th>
                        <th title="Referable">Referable</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <div class="card mt-5 card-action mb-12">
        <div class="card-header d-flex">
            <h3 class="card-action-title mb-0 flex-grow-1">Schedules</h3>
            <div class="card-action-element">
                <ul class="list-inline mb-0">
                    <li class="list-inline-item">
                        <a href="javascript:void(0);" class="card-collapsible"><i class="fa fa-caret-down"></i></a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="card-datatable collapse" data-datatable="plan-order-schedules-table">
            <table class="table table-striped table-bordered" id="plan-order-schedules-table" style="width:100%">
                <thead>
                    <tr>
                        <th title="Run at">Run at</th>
                        <th title="Interval">Interval</th>
                        <th title="No. Cycle">No. Cycle</th>
                        <th title="Status">Status</th>
                        <th title="Message">Message</th>
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
            window.LaravelDataTables["plan-order-cycles-table"] = $("#plan-order-cycles-table").DataTable({
                "serverSide": true,
                "processing": true,
                "ajax": {
                    "url": "{{ LaravelPlanning::route('orders.cycles.datatable', ['planOrder' => $planOrder]) }}",
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
                    "name": "started_at",
                    "data": "started_at",
                    "title": "Started at",
                    "orderable": true,
                    "searchable": true
                }, {
                    "name": "ended_at",
                    "data": "ended_at",
                    "title": "Ended at",
                    "orderable": true,
                    "searchable": true
                }, {
                    "name": "number_of_cycle",
                    "data": "number_of_cycle",
                    "title": "Number Of Cycle",
                    "orderable": true,
                    "searchable": true
                }, {
                    "name": "price",
                    "data": "price",
                    "title": "Price",
                    "orderable": true,
                    "searchable": true
                }, {
                    "name": "targetHtml",
                    "data": "targetHtml",
                    "title": "Target",
                    "orderable": false,
                    "searchable": false
                }, {
                    "name": "status",
                    "data": "status",
                    "title": "Status",
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
    <script type="text/javascript">
        $(function() {
            window.LaravelDataTables = window.LaravelDataTables || {};
            window.LaravelDataTables["plan-order-logs-table"] = $("#plan-order-logs-table").DataTable({
                "serverSide": true,
                "processing": true,
                "ajax": {
                    "url": "{{ LaravelPlanning::route('orders.logs.datatable', ['planOrder' => $planOrder]) }}"
                },
                "columns": [{
                    "name": "created_at",
                    "data": "created_at",
                    "title": "Time",
                    "orderable": true,
                    "searchable": true
                }, {
                    "name": "content",
                    "data": "content",
                    "title": "Content",
                    "orderable": true,
                    "searchable": true
                }, {
                    "name": "targetHtml",
                    "data": "targetHtml",
                    "title": "Referable",
                    "orderable": true,
                    "searchable": true
                }],
                "order": [
                    [0, "desc"]
                ],
                "responsive": true,
                "deferLoading": 0
            });
        });
    </script>
    <script type="text/javascript">
        $(function() {
            window.LaravelDataTables = window.LaravelDataTables || {};
            window.LaravelDataTables["plan-order-schedules-table"] = $("#plan-order-schedules-table").DataTable({
                "serverSide": true,
                "processing": true,
                "ajax": {
                    "url": "{{ LaravelPlanning::route('orders.schedules.datatable', ['planOrder' => $planOrder]) }}"
                },
                "columns": [{
                    "name": "run_at",
                    "data": "run_at",
                    "title": "Run at",
                    "orderable": true,
                    "searchable": true
                }, {
                    "name": "interval",
                    "data": "interval",
                    "title": "Interval",
                    "orderable": true,
                    "searchable": true
                }, {
                    "name": "number_of_cycle",
                    "data": "number_of_cycle",
                    "title": "No. Cycle",
                    "orderable": true,
                    "searchable": true
                }, {
                    "name": "status",
                    "data": "status",
                    "title": "Status",
                    "orderable": true,
                    "searchable": true
                }, {
                    "name": "message",
                    "data": "message",
                    "title": "Message",
                    "orderable": true,
                    "searchable": true
                }],
                "order": [
                    [0, "desc"]
                ],
                "responsive": true,
                "deferLoading": 0
            });
        });
    </script>
    <script type="module">
        $(function() {
            $('.append_elements').append($('#append_elements'));
            $('.select_filter_status').on('change', () => LaravelDataTables['plan-order-cycles-table'].ajax
            .reload());

            const c = [].slice.call(document.querySelectorAll(".card-collapsible"));
            c.map(function(e) {
                e.addEventListener("click", l => {
                    l.preventDefault();
                    const collapseEl = e.closest(".card").querySelector(".collapse");
                    const datatableId = collapseEl.dataset.datatable
                    new bootstrap.Collapse(collapseEl),
                        e.closest(".card-header").classList.toggle("collapsed");
                    const classList = e.firstElementChild.classList;

                    classList.toggle("fa-caret-down")
                    classList.toggle("fa-caret-up")

                    if (datatableId && !LaravelDataTables[datatableId].ajax.json()) {
                        LaravelDataTables[datatableId].draw();
                    }

                })
            })
        });
    </script>
    <script>
        function deleteItem(event, id) {
            event.preventDefault();
            const url = "{{ LaravelPlanning::route('destroy', ['plan' => '--id--']) }}";
            if (confirm('Are you sure?')) {
                $.ajax({
                        method: 'POST',
                        url: url.replace('--id--', id),
                        data: {
                            _method: 'DELETE'
                        }
                    })
                    .always(() => {
                        LaravelDataTables['plan-order-cycles-table'].ajax.reload()
                    })
            }
        }
    </script>
@endpush
