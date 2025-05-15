@extends('layouts.app')

@section('title','Product Development')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection
@section('content')
    <div class="mb-3">
        <h1 class="h3 d-inline align-middle">@yield('title')</h1>
        @can(\App\Enums\Core\PermissionEnum::ProductDevelopmentWrite->value, \App\Models\CRM\ProductDevelopment::class)
            <button class="btn btn-primary float-end ms-2 modal-create-product" type="button"><i
                    class="fas fa-plus-circle"></i> Add a New Product
            </button>
        @endcan
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <table id="productsTable"
                           class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Stage</th>
                            <th>Target Group</th>
                            <th>Comments</th>
                            <th>Start Date</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="ProductActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item" id="createProductModal">
                        <form method="post" id="createProductForm"
                              action="{{ route('product-development.store') }}"> @csrf
                            <div class="mb-3">
                                <label class="form-label" for="Name">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="Name" name="Name" required
                                       placeholder="Name">
                                <p id="Name_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label for="TargetGroup" class="form-label">Target Group <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="TargetGroup" name="TargetGroup" required
                                       placeholder="Target Group">
                                <p id="TargetGroup_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Notes">Notes </label>
                                <textarea name="Notes" id="Notes" rows="3" class="form-control"></textarea>
                                <p id="Notes_end_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="createProductBtn" type="submit"><i
                                        class="fas fa-save"></i> add
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')
    <script src="{{ asset('assets/js/datatables.js') }}"></script>
    <script> const $Modal = $('#ProductActionsModal');
        let productsTable = null;
        $(function () {
            //  $.fn.dataTable.ext.errMode = 'none';
            fetchProductsTable();

            $(document).on('click', '.modal-create-product', function () {
                $(".modal-title").html('Add a Product in Development');
                $(".modal-item").addClass('d-none');
                $('#createProductModal').removeClass('d-none');
                $Modal.modal('show');
            });

            $('form#createProductForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#createProductBtn'), true, true, true, false)) {
                    $Modal.modal('hide');
                }
            });
        });

        function fetchProductsTable() {
            if (productsTable === null) {
                productsTable = $('#productsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[5, 'desc']],
                    /*"columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],*/
                    ajax: {
                        url: document.url,
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: 'ProductID', name: 'ProductID'},
                        {data: 'Name', name: 'Name'},
                        {data: 'stage.Description', name: 'stage.Description'},
                        {data: 'TargetGroup', name: 'TargetGroup'},
                        {data: 'comments_count', name: 'comments_count'},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                    ], "oLanguage": {
                        "sEmptyTable": "no products found here"
                    }
                });

                productsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading products.");
                    console.log(er);
                });
            } else {
                productsTable.ajax.reload();
            }
        }
    </script>
@endsection
