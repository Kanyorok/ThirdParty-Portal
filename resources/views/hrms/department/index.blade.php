@extends('layouts.app')

@section('title','Departments')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-actions float-end">
                        <button class="btn btn-primary ms-2 click-summary-data" type="button"
                                data-click_url="{{ route('departments.create') }}"
                                data-summary_title="Create a New Department">
                            <i class="fas fa-plus"> </i> Add a Department
                        </button>
                    </div>
                    <h5 class="card-title mb-0">@yield('title')</h5>
                </div>
                <div class="card-body">
                    <table id="departmentsTable"
                           class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>
                        <tr>
                            <th>No.</th>
                            <th>Name</th>
                            <th>Employees</th>
                            <th>HOD</th>
                            <th>actions</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script> const $Modal = $('#departmentsActionsModal');
        let departmentsTable = null;
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchDepartmentsTable();
        });


        function fetchDepartmentsTable() {
            if (departmentsTable === null) {
                departmentsTable = $('#departmentsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[3, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: getDocumentUrl(),
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DepartmentID", name: 'DepartmentID'},
                        {data: 'Name', name: 'Name'},
                        {data: 'employees_count', name: 'employees_count', searchable: false, orderable: false},
                        {data: 'hod', name: 'hod', searchable: false, orderable: false},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no departments under filter"
                    }
                });

                departmentsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading departments.");
                });
            } else {
                departmentsTable.ajax.reload();
            }
        }
    </script>
@endsection
