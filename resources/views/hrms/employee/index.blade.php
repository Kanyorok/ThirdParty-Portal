@extends('layouts.app')

@section('title','Employees')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
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
                        <a class="btn btn-primary ms-2 " href="{{ route('employees.create') }}">
                            Add an Employee
                        </a>
                    </div>
                    <h5 class="card-title mb-0">@yield('title')</h5>
                </div>
                <div class="card-body">
                    <table id="employeesTable"
                           class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>
                        <tr>
                            <th>No.</th>
                            <th>Employee ID</th>
                            <th>Full Name</th>
                            <th>Job Title</th>
                            <th>Department</th>
                            {{-- <th>Phone</th>
                            <th>Email</th>--}}
                            <th>Actions</th>
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
    <script>let employeesTable = null;
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchEmployeesTable();
        });

        function fetchEmployeesTable() {
            if (employeesTable === null) {
                employeesTable = $('#employeesTable').DataTable({
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
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: "EmployeeID", name: 'EmployeeID'},
                        {data: 'Name', name: 'Name'},
                        {data: 'JobTitle', name: 'JobTitle'},
                        {data: 'Department', name: 'Department'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no employees under filter"
                    }
                });

                employeesTable.on('error', function (er) {
                    nWarning("an issue occurred while loading employees.");
                    console.log(er);
                });
            } else {
                employeesTable.ajax.reload();
            }
        }
    </script>
@endsection
