@extends('laravel_planning::layout')
@section('content')

<div class="card">
    <div class="card-header border-bottom">
            <h5>Plans List</h5>
    </div>
    <div class="card-datatable table-responsive">
        <table class="table table-striped table-bordered" id="plans-table" style="width:100%"><thead><tr><th title="#">#</th><th title="Name">Name</th><th title="Resource">Resource</th><th title="Code">Code</th><th title="Cycle">Cycle</th><th title="Interval">Interval</th><th title="Interval Count">Interval Count</th><th title="Orders">Orders</th><th title="Action">Action</th></tr></thead></table>

    </div>
</div>
<div style="display:none;">
    <div id="append_elements" class="row">
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
<script>
    $(function() {
        window.LaravelDataTables = window.LaravelDataTables || {};
        window.LaravelDataTables["plans-table"] = $("#plans-table").DataTable({
            "serverSide": true,
            "processing": true,
            "ajax": {
                "url": "{{ LaravelPlanning::route('datatable') }}",
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
                "name": "resource",
                "data": "resource",
                "title": "Resource",
                "orderable": false,
                "searchable": false
            }, {
                "name": "code",
                "data": "code",
                "title": "Code",
                "orderable": false,
                "searchable": false
            }, {
                "name": "cycle",
                "data": "cycle",
                "title": "Cycle",
                "orderable": false,
                "searchable": false
            }, {
                "name": "interval",
                "data": "interval",
                "title": "Interval",
                "orderable": false,
                "searchable": false
            }, {
                "name": "interval_count",
                "data": "interval_count",
                "title": "Interval Count",
                "orderable": false,
                "searchable": false
            }, {
                "name": "countOrdersHtml",
                "data": "countOrdersHtml",
                "title": "Orders",
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

        $('.append_elements').append($('#append_elements'));
        $('.select_filter_status').on('change', ()=>LaravelDataTables['plans-table'].ajax.reload())
    });
</script>
@endpush
