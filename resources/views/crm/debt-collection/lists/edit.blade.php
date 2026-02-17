@php use App\Enums\Core\VisibilityEnum;use Illuminate\Support\Str; @endphp
@extends('layouts.app')

@section('title')
    {{ Str::limit($list->Label,50) }} Loans List
@endsection
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/2.0.5/css/select.dataTables.css">
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">CRM</a></li>
    <li class="breadcrumb-item"><a href="{{ route('loans-list.index') }}">Loans List</a></li>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-4 ">
            <div class="card">
                <div class="card-body">
                    <h2 class="text-center">{!! $list->Visibility->icon() !!} <a
                            href="{{ route('loans-list.show',$list->slug) }}"
                            class="text-black text-decoration-underline">{{ $list->Label }}</a></h2>
                    <p class="text-center">Loans Lists</p>
                    <p class="text-center">{{ $list->Notes }}</p>

                    <hr>
                    @include('snippets.behind_scenes',['model'=>$list])

                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <button type="button" class="btn btn-primary w-100 modal-update-list">
                                <i class="fas fa-edit"></i></button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-danger w-100 modal-trash-list">
                                <i class="fas fa-trash-alt"></i></button>
                        </div>

                        <div class="col-12">
                            <a href="{{ route('loans-list.edit',[$list->slug,($remove)?'add':'remove']) }}"
                               class="btn btn-info w-100 my-2">
                                <i class="fas fa-random"></i>{{ ($remove)?' add ': ' remove ' }} accounts</a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card mb-3 border border-2 border-info">
                <div class="card-body p-0">
                    <div class="accordion accordion-flush" id="accordionFilters">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="flush-headingOne">
                                <button class="accordion-button collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#flush-filters"
                                        aria-expanded="false" aria-controls="flush-filters">
                                    Loans Filters
                                </button>
                            </h2>
                            <div id="flush-filters" class="accordion-collapse collapse"
                                 aria-labelledby="flush-headingOne" data-bs-parent="#accordionFilters">
                                <div class="accordion-body row">
                                    <form class="card-body row py-0 px-2" id="searchForm">
                                        <div class="col-md-4 col-12 mb-3">
                                            <label class="form-label" for="Status">Loan Status </label>
                                            <select name="Status[]" id="Status"
                                                    class="search-form-item form-control w-100" multiple>
                                                @foreach($LoanSubClasses as $status)
                                                    <option
                                                        value="{{ $status->SubCodeID }}">{{ $status->Description }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 col-12 mb-3">
                                            <label for="Branch" class="form-label">Branches</label>
                                            <select class="form-control" name="Branch[]" id="Branch" multiple>
                                                @foreach($branches as $branch)
                                                    <option
                                                        value="{{ $branch->OurBranchID }}">{{ Str::title($branch->BranchName) }}</option>
                                                @endforeach
                                            </select>
                                            <p id="RooMBranch_error" class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>
                                        <div class="col-md-4 col-12 mb-3">
                                            <label for="Product" class="form-label">Products</label>
                                            <select class="form-control" name="Product[]" id="Product" multiple>
                                                @foreach($Products as $Product)
                                                    <option
                                                        value="{{ $Product->ProductID }}">{{ $Product->Description }}</option>
                                                @endforeach
                                            </select>
                                            <p id="Product_error" class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>
                                        <div class="col-md-4 col-12 mb-3">
                                            <label class="form-label" for="Contacted">Not Contacted After </label>
                                            <input type="text" class="search-form-item form-control" id="Contacted"
                                                   name="Contacted" placeholder="Not Contacted After">
                                            <p id="Contacted_error" class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>
                                        <div class="col-md-4 col-12 mb-3">
                                            <label class="form-label" for="MaturityMin">Maturity Date Minimum </label>
                                            <input type="text" class="search-form-item form-control" id="MaturityMin"
                                                   name="MaturityMin" placeholder="Maturity Minimum">
                                            <p id="MaturityMin_error" class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>
                                        <div class="col-md-4 col-12 mb-3">
                                            <label class="form-label" for="MaturityMax">Maturity Date Maximum </label>
                                            <input type="text" class="search-form-item form-control" id="MaturityMax"
                                                   name="MaturityMax" placeholder="Maturity Maximum">
                                            <p id="MaturityMax_error" class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>
                                        <div class="col-md-4 col-12 mb-3">
                                            <label class="form-label" for="ArrearsAmountMin">Arrears Amount
                                                Minimum </label>
                                            <input type="number" class="search-form-item form-control"
                                                   id="ArrearsAmountMin" name="ArrearsAmountMin"
                                                   placeholder="Arrears Amount Minimum" min="0">
                                            <p id="ArrearsAmountMin_error" class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>
                                        <div class="col-md-4 col-12 mb-3">
                                            <label class="form-label" for="ArrearsAmountMax">Arrears Amount
                                                Maximum </label>
                                            <input type="number" class="search-form-item form-control"
                                                   id="ArrearsAmountMax" name="ArrearsAmountMax"
                                                   placeholder="Arrears Amount Maximum">
                                            <p id="ArrearsAmountMax_error" class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>

                                        <div class="col-md-4 col-12 mb-3">
                                            <label class="form-label" for="ArrearsDaysMin">Arrears Days Minimum </label>
                                            <input type="number" class="search-form-item form-control"
                                                   id="ArrearsDaysMin" name="ArrearsDaysMin"
                                                   placeholder="Arrears Amount Minimum" min="0">
                                            <p id="ArrearsDaysMin_error" class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>
                                        <div class="col-md-4 col-12 mb-3">
                                            <label class="form-label" for="ArrearsDaysMax">Arrears Days Maximum </label>
                                            <input type="number" class="search-form-item form-control"
                                                   id="ArrearsDaysMax" name="ArrearsDaysMax"
                                                   placeholder="Arrears Amount Maximum">
                                            <p id="ArrearsDaysMax_error" class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>

                                        <div class="col-12">
                                            <hr>
                                            <span class="float-end">
                                        <button class="btn btn-primary w-100" id="searchFormBtn" type="submit">
                                            <i class="fas fa-magnifying-glass"></i> Filter Loans
                                        </button>
                                        </span>
                                            <div class="clearfix"></div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header border-bottom border-1">
                    @if($remove)
                        <div class="row mb-0">
                            <div class="col-8 mb-0">
                                <h3 class="mb-1 mt-2">
                                    Select Loans to Remove
                                </h3>
                            </div>
                            <div class="col-4 mb-0">
                                <form action="{{ route('loans-list.accounts', [$list->slug, 'q'=> 'current']) }}"
                                      method="post"
                                      id="saveLoansToListForm">@csrf @method('put')
                                    <button class="float-end btn btn-danger disabled" type="submit"
                                            id="saveLoansToList">
                                        <i class="fas fa-save"></i> remove loans
                                    </button>
                                    <input type="hidden" name="loans" id="LoansToList" class="d-none">
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="row mb-0">
                            <div class="col-8 mb-0">
                                <h3 class="mb-1 mt-2">
                                    Select Loans to Add
                                </h3>
                            </div>
                            <div class="col-4 mb-0">
                                <form action="{{ route('loans-list.accounts', $list->slug) }}" method="post"
                                      id="saveLoansToListForm">@csrf @method('put')
                                    <button class="float-end btn btn-primary disabled" type="submit"
                                            id="saveLoansToList">
                                        <i class="fas fa-save"></i> add loans
                                    </button>
                                    <input type="hidden" name="loans" id="LoansToList" class="d-none">
                                </form>
                            </div>
                        </div>
                    @endif

                </div>
                <div class="card-body">
                    <div class="table-responsive">
                    <table id="productsTable"
                           class="table table-striped dataTable no-footer dtr-inline w-100">
                        <thead>
                        <tr>
                            <th></th>
                            <th>AccountId</th>
                            <th>Client</th>
                            <th>Status</th>
                            <th>Balance</th>
                            <th>Arrears Days</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table></div>
                </div>
            </div>

        </div>
    </div>
    <div class="modal fade" id="ListActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item" id="updateListModal">
                        <form action="{{ route('loans-list.update',[$list->slug]) }}" method="post"
                              id="updateListForm"> @csrf
                            <div class="mb-3">@method('put')
                                <label class="form-label" for="Label">Label <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="Label" name="Label" required
                                       placeholder="Label" value="{{ $list->Label }}">
                                <p id="Label_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Visibility">Visibility <span class="text-danger">*</span></label>
                                <select class="form-control" name="Visibility" id="Visibility" required>
                                    @foreach(VisibilityEnum::cases() as $Visibility)
                                        <option
                                            value="{{ $Visibility->value }}" {{ ($Visibility->value===$list->Visibility->value)?'selected':'' }}>{!! $Visibility->icon() !!} {{ $Visibility->description() }}</option>
                                    @endforeach
                                </select>
                                <p id="Party_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Notes">Notes </label>
                                <textarea name="Notes" id="Notes" rows="3" class="form-control"
                                          maxlength="1000">{{ $list->Notes }}</textarea>
                                <p id="Notes_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="updateListBtn" type="submit"><i
                                        class="fas fa-save"></i>
                                    update {{ Str::limit($list->Label ,20) }}
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item text-center" id="trashListModal">
                        <h4 class="text-danger">
                            Trash Loans List <b>{{ $list->Label }}</b> ?
                        </h4>
                        <form id="trashListForm" method="post"
                              action="{{ route('loans-list.destroy',[$list->slug]) }}"> @csrf @method('delete')
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    no, cancel
                                </button>
                                <button class="btn btn-danger float-end" id="trashListBtn"
                                        type="submit"><i
                                        class="fas fa-trash"></i> yes, trash
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
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/select/2.0.5/js/dataTables.select.js"></script>
    <script src="https://cdn.datatables.net/select/2.0.5/js/select.dataTables.js"></script>
    <script src='{{ asset('assets/libs/moment/moment-with-locales.js') }}'></script>
    <script>const searchBtn = $('#searchFormBtn'), searchQuery = $('.search-form-item');
        let productsTable = null;

        const loansBtn = $("#saveLoansToList"), $Modal = $('#ListActionsModal');
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';

            $('form#searchForm').submit(function (e) {
                e.preventDefault();
                fetchProductsTable();
                searchBtn.addClass('disabled');
                searchQuery.addClass('disabled');
            });

            $('#Status').select2({
                allowClear: true,
                placeholder: "Loan Status(s)",
            });
            $('#Product').select2({
                allowClear: true,
                placeholder: "Product(s)",
            });
            $('#Branch').select2({
                allowClear: true,
                placeholder: "Branch(s)",
            });
            flatpickr("#MaturityMin", {
                enableTime: false,
                altInput: true,
                allowInput: true,
                altFormat: "F j, Y",
                dateFormat: "Y-m-d",
            });
            flatpickr("#Contacted", {
                enableTime: false,
                altInput: true,
                allowInput: true,
                altFormat: "F j, Y",
                dateFormat: "Y-m-d",
                maxDate: moment().format('YYYY-MM-DD'),
            });
            flatpickr("#MaturityMax", {
                enableTime: false,
                altInput: true,
                allowInput: true,
                altFormat: "F j, Y",
                dateFormat: "Y-m-d",
            });


            $(document).on('click', '.modal-trash-list', function () {
                $(".modal-title").html('Trash List : {{ $list->Label }}');
                $(".modal-item").addClass('d-none');
                $('#trashListModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#trashListForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#trashListBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.modal-update-list', function () {
                $(".modal-title").html('Update List : {{ $list->Label }}');
                $(".modal-item").addClass('d-none');
                $('#updateListModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#updateListForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#updateListBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $('form#saveLoansToListForm').submit(async function (e) {
                e.preventDefault();
                let list = $("#LoansToList");
                if (productsTable !== null) {
                    list.val($.map(productsTable.rows({selected: true}).data(), function (item) {
                        return item.AccountID;
                    }).join(","));
                    if (await saveForm($(this), loansBtn, false, true, true)) {
                        fetchProductsTable();
                    }
                }
            });
            fetchProductsTable();
        });

        function getUrl(base) {
            return base + "?q={{ ($remove)?'current':'add' }}&Contacted=" + $('#Contacted').val() + "&MaturityMin=" + $('#MaturityMin').val() + "&MaturityMax=" + $('#MaturityMax').val() + "&ArrearsDaysMin=" + $('#ArrearsDaysMin').val() + "&ArrearsDaysMax=" + $('#ArrearsDaysMax').val() + "&ArrearsAmountMax=" + $('#ArrearsAmountMax').val() + "&ArrearsAmountMin=" + $('#ArrearsAmountMin').val() + "&Status=" + $('#Status').val() + "&Branch=" + $('#Branch').val() + "&dated={{ ($dated instanceof Carbon\Carbon)? $dated->format('U'):0 }}";
        }

        function fetchProductsTable() {
            if (productsTable === null) {
                productsTable = $('#productsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    select: {
                        style: 'multi',
                        selector: 'td:first-child',
                        headerCheckbox: 'select-page'
                    },
                    dom: '<"row"<"col-12 mb-2"lr><"col-12 mb-2"t><"col-12 text-center"p>>',
                    ajax: {
                        url: getUrl('{{ route('loans-list.accounts', $list->slug) }}'),
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    initComplete: function () {
                        searchBtn.removeClass('disabled').html('<i class="fas fa-magnifying-glass"></i> Filter Loans');
                        searchQuery.removeClass('disabled');
                    },
                    columns: [
                        {data: null, orderable: false, searchable: false, render: DataTable.render.select()},
                        {data: 'AccountID', name: 'AccountID'},
                        {data: 'ClientID', name: 'ClientID'},
                        {data: 'Classification', name: 'Classification'},
                        {data: 'OutstandingBalance', name: 'OutstandingBalance'},
                        {data: 'ArrearsDays', name: 'ArrearsDays'},
                    ], "oLanguage": {
                        "sEmptyTable": "no loans found here."
                    }
                }).on('select', function () {
                    if (productsTable.rows({selected: true}).count() === 0) {
                        loansBtn.addClass('disabled');
                    } else {
                        loansBtn.removeClass('disabled');
                    }
                }).on('deselect', function () {
                    if (productsTable.rows({selected: true}).count() === 0) {
                        loansBtn.addClass('disabled');
                    } else {
                        loansBtn.removeClass('disabled');
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
