@extends('layouts.app')

@section('title','Accounts')
@section('styles')
    <style>
        .mouse_pointer {
            cursor: pointer;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body row justify-content-md-center">
                    <div class="col-12 col-md-8 col-lg-6">
                        <form class="m-3" id="searchForm">
                            <div class="input-group input-group-lg">
                                <input type="search" class="form-control" name="q" autocomplete="off" disabled maxlength="50" id="searchFormQ" placeholder="Search for client id, account no, name">
                                <button class="btn btn-primary" id="searchFormBtn" disabled type="submit"><i class="fas fa-magnifying-glass"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <table id="accountsTable" class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>
                        <tr>
                            <th>Account No</th>
                            <th>Branch</th>
                            <th>Name</th>
                            <th>Product</th>
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

    <script>const searchBtn =  $('#searchFormBtn'), searchQuery =  $('#searchFormQ');
        let accountsTable = null;
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';

            $('form#searchForm').submit(function (e) {
                e.preventDefault();
                fetchTable();
                searchBtn.prop('disable', true);
                searchQuery.prop('disabled', true);
            });

            fetchTable();
        });

        function getUrl() {
            return getDocumentUrl() + '?q=' + searchQuery.val();
        }


        function fetchTable() {
            searchBtn.html('<i class="fas fa-spinner fa-spin"></i> please wait')
            if (accountsTable === null) {
                accountsTable = $('#accountsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    dom:'tir',
                    ajax: {
                        url: getUrl(),
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    initComplete: function (settings, json) {
                        searchBtn.removeAttr('Disabled').html('<i class="fas fa-magnifying-glass"></i>');
                        searchQuery.prop('disabled', false);
                    },
                    columns: [
                        {data: 'AccountID', name: 'AccountID'},
                        {
                            data: 'branch.BranchName',
                            "mRender": function (data, type, full) {
                                if(full.branch.BranchID) {
                                    return full.branch.BranchID + ' - ' + full.branch.BranchName;
                                }
                                return full.OurBranchID.toString().toUpperCase();
                            }

                        },
                        {data: 'Name', name: 'Name'},
                        {
                            data: 'product.Description',
                            "mRender": function (data, type, full) {
                                if(full.product.ProductID) {
                                    return full.product.ProductID + ' - ' + full.product.Description;
                                }
                                return full.ProductID.toString().toUpperCase();
                            }

                        },
                        {data: 'status.Description', name: 'status.Description'},
                    ], "oLanguage": {
                        "sEmptyTable": "<div class='text-center'><img class='img-fluid' style='height:30vh' src='{{ asset('assets/img/errors/404.svg') }}' alt='?'></div>"
                    }
                });

                accountsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading the list.");
                    console.log(er);
                });
            } else {
                accountsTable.clear().destroy();
                accountsTable = null;
                fetchTable();
               /* accountsTable.ajax.reload();*/
            }
        }
    </script>
@endsection
