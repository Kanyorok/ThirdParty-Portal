@php use App\Models\BR\DebtProduct; @endphp
@extends('layouts.app')

@section('title','Debt Collection')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">CRM</a></li>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <h1 class="h3 d-inline align-middle"> <span class="small float-end">Dated:
                <span
                    class="text-decoration-underline">{{ ($dated instanceof Carbon\Carbon)? $dated->format('M d, Y'):'?' }}</span>
                </span>
            </h1>
            <div class="clearfix"></div>
        </div>
    </div>
    <form id="searchForm" class="card">
        <div class="card-body row">
            <div class="col-sm-2 col-12">
                <div class="mx-1 mb-2">
                    <input type="number" class="form-control w-100 search-form-item"
                           name="arrears" autocomplete="off" min="1"
                           id="arrears"
                           placeholder="arrears Days : 1" value="1">
                </div>
            </div>
            <div class="col-sm-2 col-12">
                <div class="mx-1 mb-2">
                    <select name="status" id="status" class="form-control w-100 search-form-item" required>
                        <option value="all" selected>All - Status</option>
                        @foreach($LoanSubClasses as $status)
                            <option value="{{ $status->SubCodeID }}">{{ $status->Description }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-sm-2 col-6">
                <div class="mx-1 mb-2">
                    <input type="number" class="form-control w-100 search-form-item"
                           name="balance" autocomplete="off" maxlength="50" id="balance"
                           placeholder="balance > Greater Than" min="1">
                </div>
            </div>
            <div class="col-sm-2 col-6">
                <div class="mx-1 mb-2">
                    <input type="search" class="form-control w-100 search-form-item"
                           name="member_no" autocomplete="off" maxlength="50"
                           id="member_no" placeholder="member no">
                </div>
            </div>
            <div class="col-sm-2 col-6">
                <div class="mx-1 mb-2">
                    <div class="mx-1 mb-2">
                        <select class="form-control w-100 filter-field" name="assignee" id="assignee">
                            @can('assign', DebtProduct::class)
                                <option value="all">Assigned: Any</option>
                                <option value="none">Assigned: None</option>
                            @endcan
                            <option selected value="{{ auth()->user()->UserID }}">
                                Assigned: {{ auth()->user()->UserID }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-sm-2 col-6">
                <button class="btn btn-primary w-100" id="searchFormBtn" type="submit"><i
                        class="fas fa-magnifying-glass"></i>
                </button>
            </div>
        </div>


    </form>
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="table-responsive">
                    <table id="productsTable"
                           class="table table-striped dataTable no-footer dtr-inline w-100 ">
                        <thead>
                        <tr>
                            <th>AccountId</th>
                            <th>Product</th>
                            <th>Client</th>
                            <th>Status</th>
                            <th>Balance</th>
                            <th>Arrears Days</th>
                            <th>Maturity Date</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')

    <script>const searchBtn = $('#searchFormBtn'), searchQuery = $('.search-form-item');
        let productsTable = null;
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            $('form#searchForm').submit(function (e) {
                e.preventDefault();
                fetchProductsTable();
                searchBtn.addClass('disabled');
                searchQuery.addClass('disabled');
            });

            fetchProductsTable();
        });

        function fetchProductsTable() {
            if (productsTable === null) {
                productsTable = $('#productsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    dom: '<"row"<"col-12 mb-2"tr><"col-3"l><"col-5 text-center"i><"col-4"p>>',
                    "order": [[6, 'desc']],
                    /*"columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],*/
                    ajax: {
                        url: getDocumentUrl() + "?member_no=" + $('#member_no').val() + "&arrears=" + $('#arrears').val() + "&assignee=" + $('#assignee').val() + "&status=" + $('#status').val() + "&balance=" + $('#balance').val() + "&dated={{ ($dated instanceof Carbon\Carbon)? $dated->format('U'):0 }}",
                        error: function (request) {
                            if (request.status === 400 && request.responseJSON.message) {
                                nWarning(request.responseJSON.message);
                            } else {
                                codeNotify(request.status);
                            }
                        }
                    },
                    initComplete: function () {
                        searchBtn.removeClass('disabled').html('<i class="fas fa-magnifying-glass"></i>');
                        searchQuery.removeClass('disabled');
                    },
                    columns: [
                        {data: 'AccountID', name: 'AccountID'},
                        {data: 'ProductName', name: 'ProductName'},
                        {data: 'ClientID', name: 'ClientID'},
                        {data: 'Classification', name: 'Classification'},
                        {data: 'OutstandingBalance', name: 'OutstandingBalance'},
                        {data: 'ArrearsDays', name: 'ArrearsDays'},
                        {data: 'MaturityDate', name: 'MaturityDate'},
                    ], "oLanguage": {
                        "sEmptyTable": "no loans found here."
                    }
                });

                productsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading loans.");
                });
            } else {
                productsTable.clear().destroy();
                productsTable = null;
                fetchProductsTable();
            }
        }
    </script>
@endsection
