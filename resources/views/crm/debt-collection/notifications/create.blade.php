@php use App\Enums\Loan\LoanCategorizationEnum; @endphp
@extends('layouts.app')

@section('title','Bulk Notifications')
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
    <li class="breadcrumb-item"><a href="{{ route('debt-notification.index') }}">Debt Notifications</a></li>
@endsection
@section('content')
    <div class="row">
        <div class="col-12 mb-3">
            <h3>
                <span class="small float-end">Dated:
              <span
                  class="text-decoration-underline">{{ ($dated instanceof Carbon\Carbon)? $dated->format('M d, Y'):'?' }}</span>
            </span>
            </h3>
        </div>
        <div class="col-12">
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
                            <div id="flush-filters" class="accordion-collapse collapse show"
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
                                                        value="{{ $branch->OurBranchID }}">{{ \Illuminate\Support\Str::title($branch->BranchName) }}</option>
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
                                            <label for="Categorization" class="form-label">Categorization</label>
                                            <select class="form-control" name="Categorization" id="Categorization">
                                                <option value="all">All</option>
                                                @foreach(LoanCategorizationEnum::getAll() as $cat)
                                                    <option
                                                        value="{{ $cat->name }}">{{ $cat->description() }}</option>
                                                @endforeach
                                            </select>
                                            <p id="Product_error" class="invalid-feedback d-none error col-12"
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
        </div>

        <div class="col-sm-12">
            <div class="card">
                <div class="card-body py-0">
                    <ul class="nav nav-tabs profile-tabs" id="employeeTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="profile-tab-1" data-bs-toggle="tab" href="#tab-0" role="tab"
                               aria-selected="false" tabindex="-1">
                                Notification </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link " id="profile-tab-2" href="#tab-1" data-bs-toggle="tab" role="tab"
                               aria-selected="false">
                                Loans Data </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="tab-content">
                <div class="tab-pane active show" id="tab-0" role="tabpanel" aria-labelledby="profile-tab-1">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><h5>Notification Details</h5></div>
                                <div class="card-body">
                                    <div class="accordion accordion-flush mb-3" id="accordionHelp">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="flush-headingOne">
                                                <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#flush-collapseOne"
                                                        aria-expanded="false" aria-controls="flush-collapseOne">
                                                    Help Notes
                                                </button>
                                            </h2>
                                            <div id="flush-collapseOne" class="accordion-collapse collapse"
                                                 aria-labelledby="flush-headingOne" data-bs-parent="#accordionHelp">
                                                <div class="accordion-body">
                                                    <ul class="loanee-group loanee-group-flush">
                                                        <li class="loanee-group-item">You can use <code> #name</code> to
                                                            be
                                                            replaced by their name while sending.
                                                        </li>
                                                        <li class="loanee-group-item">You can use <code> #amount</code>
                                                            to be
                                                            replaced with Amount in Arrears while sending.
                                                        </li>
                                                        <li class="loanee-group-item">You can use <code> #arrears</code>
                                                            to
                                                            be
                                                            replaced by Days in Arrears while sending.
                                                        </li>
                                                        <li class="loanee-group-item">You can use <code> #account</code>
                                                            to
                                                            be replaced by Loan Account ID while sending.
                                                        </li>
                                                        <li class="loanee-group-item">You can use <code> #product</code>
                                                            to
                                                            be replaced by Loan Product Name while sending.
                                                        </li>
                                                        <li class="loanee-group-item">You can use <code> #date</code> to
                                                            be
                                                            replaced
                                                            by {{ ($dated instanceof Carbon\Carbon)? $dated->format('M d, Y'):'?' }}
                                                            while
                                                            sending.
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <form method="post" id="debtBulkNotificationForm">
                                        <div class="col-12 mb-3">@csrf
                                            <label class="form-label" for="Label">Label <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="search-form-item form-control" required
                                                   id="Label" name="Label"
                                                   placeholder="Label">
                                            <p id="v_error" class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>
                                        <div class="mb-3 col-12">
                                            <label class="form-label" for="Content">Content <span
                                                    class="text-danger">*</span></label>
                                            <b class="float-end text-info" id="msgCounter"></b>
                                            <textarea name="Content" id="Content" class="form-control" rows="4"
                                                      maxlength="50000" minlength="2"></textarea>
                                            <p id="Content_error" class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>
                                        <hr>
                                        <button type="submit" class="float-end btn btn-success w-50 "
                                                id="debtBulkNotificationBtn"><i class="fa fa-plane-departure"></i> send
                                            messages
                                        </button>
                                        <div class="clearfix"></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="tab-1" role="tabpanel" aria-labelledby="profile-tab-2">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><h5>Loan Details</h5></div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="productsTable"
                                               class="table table-striped dataTable no-footer dtr-inline w-100">
                                            <thead>
                                            <tr>
                                                <th>AccountId</th>
                                                <th>Client</th>
                                                <th>Status</th>
                                                <th>Balance</th>
                                                <th>Product Name</th>
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
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')

    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script src='{{ asset('assets/libs/moment/moment-with-locales.js') }}'></script>
    <script>const searchBtn = $('#searchFormBtn'), searchQuery = $('.search-form-item');
        let productsTable = null, dataRoute = '';
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            $('form#debtBulkNotificationForm').submit(async function (e) {
                e.preventDefault();
                $(this).attr('action', getUrl('{{ route('debt-notification.store') }}'));
                await saveForm($(this), $('#debtBulkNotificationBtn'), true, true, true);
            });

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

            $('#Categorization').select2({
                placeholder: "Loan Categorization",
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

            fetchProductsTable();
        });

        function getUrl(base) {
            return base + "?Contacted=" + $('#Contacted').val() + "&MaturityMin=" + $('#MaturityMin').val() + "&MaturityMax=" + $('#MaturityMax').val() + "&ArrearsDaysMin=" + $('#ArrearsDaysMin').val() + "&ArrearsDaysMax=" + $('#ArrearsDaysMax').val() + "&ArrearsAmountMax=" + $('#ArrearsAmountMax').val() + "&ArrearsAmountMin=" + $('#ArrearsAmountMin').val() + "&Status=" + $('#Status').val() + "&Categorization=" + $('#Categorization').val() + "&Product=" + $('#Product').val() + "&Branch=" + $('#Branch').val() + "&dated={{ ($dated instanceof Carbon\Carbon)? $dated->format('U'):0 }}";
        }

        function fetchProductsTable() {
            if (productsTable === null) {
                productsTable = $('#productsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    width: '100%',
                    dom: '<"row"<"col-12 mb-1 text-center"i><"col-12 mb-2"tr><"col-12"p>>',
                    ajax: {
                        url: getUrl(getDocumentUrl()),
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    initComplete: function () {
                        searchBtn.removeClass('disabled').html('<i class="fas fa-magnifying-glass"></i> Filter Loans');
                        searchQuery.removeClass('disabled');
                    },
                    columns: [
                        {data: 'AccountID', name: 'AccountID'},
                        {data: 'ClientID', name: 'ClientID'},
                        {data: 'Classification', name: 'Classification'},
                        {data: 'OutstandingBalance', name: 'OutstandingBalance'},
                        {data: 'ProductName', name: 'ProductName'},
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
