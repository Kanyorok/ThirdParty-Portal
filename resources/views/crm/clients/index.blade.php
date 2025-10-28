@extends('layouts.app')

@section('title','Clients')
@section('styles')
    <style>
        .mouse_pointer {
            cursor: pointer;
        }
    </style>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">CRM</a></li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <form class="card-body row  px-2" id="searchForm">
                    <div class="col-12 col-md-3"><input type="search" class="form-control w-100 search-form-item"
                                                        name="id_number" autocomplete="off" maxlength="50"
                                                        id="id_number"
                                                        placeholder="id number or Cert No"></div>
                    <div class="col-12 col-md-2"><input type="search" class="form-control w-100 search-form-item"
                                                        name="member_no" autocomplete="off" maxlength="50"
                                                        id="member_no" placeholder="ClientID"></div>
                    <div class="col-12 col-md-3"><input type="search" class="form-control w-100 search-form-item"
                                                        name="phone" autocomplete="off" maxlength="50" id="phone"
                                                        placeholder="phone e.g., +12025550123"></div>
                    <div class="col-12 col-md-3"><input type="search" class="form-control w-100 search-form-item"
                                                        name="name" autocomplete="off" maxlength="50" id="name"
                                                        placeholder="name"></div>

                    <div class="col-12 col-md-1">
                        <button class="btn btn-primary w-100" id="searchFormBtn" type="submit"><i
                                class="fas fa-magnifying-glass"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <table id="clientsTable"
                           class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>
                        <tr>
                            <th style="width: 25px;">ClientID</th>
                            <th>Name</th>
                            <th>ID / Reg No.</th>
                            <th>Type</th>
                            <th>Phone</th>
                            <th>Status</th>
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
    <script>const searchBtn = $('#searchFormBtn'), searchQuery = $('.search-form-item');
        let clientsTable = null;
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';

            $('form#searchForm').submit(function (e) {
                e.preventDefault();
                fetchTable();
                searchBtn.addClass('disabled');
                searchQuery.addClass('disabled');
            });

            fetchTable();
        });

        function getUrl() {
            return getDocumentUrl() + '?q=' + searchQuery.val();
        }


        function fetchTable() {
            searchBtn.html('<i class="fas fa-spinner fa-spin"></i>')
            if (clientsTable === null) {
                clientsTable = $('#clientsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    dom: '<"row"<"col-12"r><"col-12 w-100 my-3"t><"col-6"i><"col-6"p>>',
                    ajax: {
                        url: getDocumentUrl() + "?member_no=" + $('#member_no').val() + "&id_number=" + $('#id_number').val() + "&phone=" + $('#phone').val() + "&name=" + $('#name').val(),
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    initComplete: function () {
                        searchBtn.removeClass('disabled').html('<i class="fas fa-magnifying-glass"></i>');
                        searchQuery.removeClass('disabled');
                    },
                    columns: [
                        {data: 'ClientID', name: 'ClientID'},
                        {data: 'Name', name: 'Name'},
                        {data: 'GovtNo', name: 'GovtNo'},
                        {data: 'type.Description', name: 'type.Description'},
                        {data: 'Mobile', name: 'Mobile'},
                        {data: 'status.Description', name: 'status.Description'},
                        /* {data: 'accounts_count', name: 'accounts_count', orderable: false, searchable: false},*/
                    ], "oLanguage": {
                        "sEmptyTable": "<div class='text-center'><img class='img-fluid' style='height:30vh' src='{{ asset('assets/img/errors/404.svg') }}' alt='?'></div>"
                    }
                });

                $("#clientsTable_filter").addClass('d-none');
                clientsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading the list.");
                    console.log(er);
                });
            } else {
                clientsTable.clear().destroy();
                clientsTable = null;
                fetchTable();
                /* clientsTable.ajax.reload();*/
            }
        }
    </script>
@endsection
