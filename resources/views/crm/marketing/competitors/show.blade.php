@php use App\Enums\LocalityTypeEnum; @endphp
@extends('layouts.app')

@section('title')
    {{ $competitor->CompetitorName }}
@endsection
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
    <li class="breadcrumb-item"><a href="{{ route('competitors.index') }}">Competitors</a></li>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-4 col-xl-3">
            <div class="card">
                <div class="card-body row">
                    <div class="col-12 col-md-4 text-center">
                        {!! $competitor->getImage('alt=".." class="img-fluid me-2"',true) !!}
                    </div>
                    <div class="col-12 col-md-8">
                        <h3>{{ $competitor->CompetitorName }} </h3>
                        <p><a href="{{ ($competitor->Website)??'#' }}"
                              target="_blank">{{ ($competitor->Website)??'www.' }}</a></p>
                        <p>{{ ($competitor->Phone) }}
                            <span class="float-end">{{ $competitor->Email }}</span>
                        </p>
                    </div>

                    <div class="col-12 my-2">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3"><i class="align-middle" data-feather="pie-chart"></i>&nbsp;<span>Market Share : </span>
                                <b class="float-end">{{ $competitor->MarketShare }}</b></li>
                            <li class="mb-3"><i class="align-middle"
                                                data-feather="users"></i>&nbsp;<span>Clients : </span> <b
                                    class="float-end">{{ number_format($competitor->Clients) }}</b></li>
                            <li class="mb-3"><i class="align-middle" data-feather="git-commit"></i>&nbsp;<span>Core Business : </span>
                                <b class="float-end">{{ $competitor->CoreBusiness }}</b></li>
                            <li class="mb-3"><i class="align-middle" data-feather="map-pin"></i> {{ $location }}</li>
                            <li class="mb-3">Notes: <i class="align-middle" data-feather="info"></i> <br> <span
                                    style="text-align: justify">{{ $competitor->Notes }}</span></li>
                        </ul>
                    </div>
                </div>
                @if(!$hasProgress)
                    <div class="card-body pt-0 row">
                        <div class="col-sm-4 col-12">
                            <button class="w-100 btn btn-danger modal-trash-competitor my-1" type="button"><i
                                    class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                        <div class="col-sm-4 col-12">
                            <button class="w-100 btn btn-primary modal-update-competitor  my-1" type="button"><i
                                    class="fas fa-edit"></i>
                            </button>
                        </div>
                        <div class="col-sm-4 col-12">
                            @can('llm', $competitor)
                                <button type="button" class="w-100 btn btn-info my-1" id="triggerFetchDataBtn"><i
                                        class="fas fa-magic-wand-sparkles"></i>
                                </button>
                            @else
                                <button class="w-100 btn btn-info disabled  my-1" type="button"><i
                                        class="fas fa-magic-wand-sparkles"></i>
                                </button>
                            @endcan
                        </div>

                    </div>
                @endif
            </div>
        </div>
        <div class="col-md-8 col-xl-9">
            @if($hasProgress)
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">Processing Data</h3>
                    </div>
                    <div class="card-body pt-0">
                        <div class="progress mb-3" style="height: 20px;">
                            <div id="processingProgress"
                                 class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                 style="width: 0" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="tab">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" href="#tab-0" data-bs-toggle="tab" role="tab"
                                            aria-selected="false" onclick="fetchCompetitorProducts()">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tab-1" data-bs-toggle="tab" role="tab"
                                            aria-selected="false" onclick="fetchSnW()">Strengths & Weaknesses</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tab-2" data-bs-toggle="tab" role="tab"
                                            aria-selected="false" onclick="fetchCustomerServicePerception()">Customer
                            Service Perception</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active m-2" id="tab-0" role="tabpanel">
                        <h3 class="mb-1 mt-2">Products
                            <button class="float-end btn btn-primary add-product-modal" type="button"><i
                                    class="fas fa-plus"></i> add product
                            </button>
                        </h3>
                        <hr class="mt-0 mb-2">
                        <table id="competitorProductsTable"
                               class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Loan Limit</th>
                                <th>Interest Rate</th>
                                <th>No of Clients</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="tab-pane m-2" id="tab-1" role="tabpanel">
                        <h3 class="mb-1 mt-2"><a href="#" id="FetchStrength" onclick="fetchCompetitorStrengths()">Strengths</a>
                            <button class="float-end btn btn-primary add-s_w-modal" type="button" data-type="Strength">
                                <i
                                    class="fas fa-plus"></i> add strength
                            </button>
                        </h3>
                        <hr class="mt-0 mb-2">
                        <table id="StrengthTable"
                               class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th class="w-75">Description</th>
                                <th>actions</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>

                        <h3 class="mb-1 mt-5"><a href="#" id="FetchWeaknesses" onclick="fetchCompetitorWeaknesses()">Weaknesses</a>
                            <button class="float-end btn btn-primary add-s_w-modal" type="button"
                                    data-type="Weaknesses"><i
                                    class="fas fa-plus"></i> add weakness
                            </button>
                        </h3>
                        <hr class="mt-0 mb-2">
                        <table id="WeaknessesTable"
                               class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th class="w-75">Description</th>
                                <th>actions</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="tab-pane m-2" id="tab-2" role="tabpanel">
                        <h3 class="mb-1 mt-2"><a href="#" id="FetchCustomerServicePerception"
                                                 onclick="fetchCustomerServicePerception()">Customer Service
                                Perception</a>
                            <button class="float-end btn btn-primary add-s_w-modal" type="button"
                                    data-type="CustomerServicePerception">
                                <i class="fas fa-plus"></i> add customer perception
                            </button>
                        </h3>
                        <hr class="mt-0 mb-2">
                        <table id="CustomerServicePerceptionTable"
                               class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th class="w-75">Description</th>
                                <th>actions</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="CompetitorActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item" id="FetchDataModal">
                        <form action="{{ route('competitor-fetch-online.store',[$competitor->CompetitorID]) }}"
                              method="post" id="FetchDataForm">
                            <div class="mb-3"> @csrf
                                <label class="form-label" for="clear">Clear Existing Products <span class="text-danger">*</span></label>
                                <select name="clear" id="clear" class="form-control w-100" required>
                                    <option value="yes">Yes, Clear Products</option>
                                    <option value="no"> No, Add to Existing Products</option>
                                </select>
                                <p id="clear_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="FetchDataBtn" type="submit">
                                    <i class="fas fa-magic-wand-sparkles"></i> fetch data
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item" id="CreateProductModal">
                        <form action="{{ route('competitor-products.store',[$competitor->CompetitorID]) }}"
                              method="post" id="CreateProductForm">
                            <div class="mb-3"> @csrf
                                <label class="form-label" for="Name">Name <span class="text-danger">*</span></label>
                                <input type="text" id="Name" name="Name" required class="form-control">
                                <p id="Name_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Limit">Limit <span class="text-danger">*</span></label>
                                <input type="number" id="Limit" name="Limit" required class="form-control">
                                <p id="Limit_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class=" mb-3">
                                <label class="form-label" for="InterestRate">Interest Rate <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" id="InterestRate" name="InterestRate" required step="0.01"
                                           min="0" max="100" class="form-control">
                                    <span class="input-group-text">%</span></div>
                                <p id="InterestRate_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="OtherCharges">Other Charges <span
                                        class="text-danger">*</span></label>
                                <input type="number" id="OtherCharges" name="OtherCharges" required class="form-control"
                                       min="0" value="0">
                                <p id="OtherCharges_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class=" mb-3">
                                <label class="form-label" for="RepaymentPeriod">Repayment Period <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" id="RepaymentPeriod" name="RepaymentPeriod" required step="1"
                                           min="1" value="1" class="form-control">
                                    <span class="input-group-text">Weeks</span></div>
                                <p id="RepaymentPeriod_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="SecurityRequired">Security Required </label>
                                <input type="text" id="SecurityRequired" name="SecurityRequired" class="form-control">
                                <p id="SecurityRequired_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Clients"> Clients <span
                                        class="text-danger">*</span></label>
                                <input type="number" id="Clients" name="Clients" required class="form-control" min="0"
                                       value="0">
                                <p id="Clients_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Notes">Notes </label>
                                <textarea name="Notes" id="Notes" class="form-control" rows="2"
                                          maxlength="5000"></textarea>
                                <p id="Notes_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="CreateProductBtn" type="submit"><i
                                        class="fas fa-plus-circle"></i> add product
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item" id="CreateDescriptionModal">
                        <form method="post" id="CreateDescriptionForm"> @csrf
                            <div class="mb-3">
                                <label class="form-label" for="ItemDescription">Description </label>
                                <textarea name="ItemDescription" id="ItemDescription" class="form-control" rows="2"
                                          required
                                          maxlength="5000"></textarea>
                                <p id="ItemDescription_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="CreateDescriptionBtn" type="submit"><i
                                        class="fas fa-plus-circle"></i> add
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content d-none modal-item text-center" id="deleteItemModal">
                        <h4 class="text-danger">
                            Delete <b id="deleteItem"></b> ?
                        </h4>
                        <div class="mt-2 mb-2">
                            You are about to delete this item, confirm below ?
                        </div>
                        <hr>
                        <form id="deleteItemForm" method="post"> @csrf @method('delete')
                            <div class="mt-4">
                                <button type="button" class="btn btn-success float-start"
                                        data-bs-dismiss="modal">
                                    no, keep
                                </button>
                                <button class="btn btn-danger float-end" id="deleteItemBtn" type="submit"><i
                                        class="fas fa-trash"></i> yes, trash
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content d-none modal-item text-center" id="trashCompetitorModal">
                        <h4 class="text-danger">
                            Delete Competitor <b id="">{{ $competitor->CompetitorName }}</b> ?
                        </h4>
                        <div class="mt-2 mb-2">
                            You are about to delete this item, confirm below ?
                        </div>
                        <hr>
                        <form id="trashCompetitorForm" method="post"
                              action="{{ route('competitors.destroy',[$competitor->CompetitorID]) }}"> @csrf @method('delete')
                            <div class="mt-4">
                                <button type="button" class="btn btn-success float-start"
                                        data-bs-dismiss="modal">
                                    no, keep
                                </button>
                                <button class="btn btn-danger float-end" id="trashCompetitorBtn" type="submit"><i
                                        class="fas fa-trash"></i> yes, trash
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item" id="updateCompetitorModal">
                        <div class="progress mb-3 avatar-change d-none">
                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                 role="progressbar" id="progress-bar" style="width: 0" aria-valuenow="0"
                                 aria-valuemin="0" aria-valuemax="100"><small class="sr-only">0%
                                    Complete</small></div>
                        </div>
                        <form action="{{ route('competitors.update',[ $competitor->CompetitorID]) }}" method="post"
                              id="updateCompetitorForm"
                              enctype="multipart/form-data"> @csrf
                            <div class="row">@method('put')
                                <div class="col-sm-6 col-12  mb-3 text-center">
                                    <input type="file" name="image" class="d-none" accept="image/*"
                                           style="display: none;" id="Upload_image">
                                    <label for="Upload_image">
                                        {!! $competitor->getImage('id="image_upload_preview" alt=".." class="img-fluid img-thumbnail mb-2"
                                             width="200" height="200"',true) !!}</label>
                                    <p id="image_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                                </div>
                                <div class="col-sm-6 col-12">
                                    <div class="mb-3">
                                        <label class="form-label" for="Name">Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="Name" name="Name" required
                                               placeholder="Name" value="{{ $competitor->CompetitorName }}">
                                        <p id="Name_error" class="invalid-feedback d-none error col-12"
                                           role="alert"></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="Website">Website </label>
                                        <input type="url" class="form-control" id="Website" name="Website"
                                               placeholder="https://craftsillicon.com"
                                               value="{{ $competitor->Website }}">
                                        <p id="Website_error" class="invalid-feedback d-none error col-12"
                                           role="alert"></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="Email">Email </label>
                                        <input type="text" class="form-control" id="Email" name="Email"
                                               placeholder="Email" value="{{ $competitor->Email }}">
                                        <p id="Email_error" class="invalid-feedback d-none error col-12"
                                           role="alert"></p>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-12 mb-3">
                                    <label for="Country" class="form-label">Country <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control" name="Country" id="Country" required>
                                        @foreach($Countries as $Country)
                                            <option value="{{ $Country->CountryCode }}"
                                                    {{ ($Country->Id === $competitor->CountryId)?'selected':'' }} data-phone="{{$Country->PhoneCode}}"
                                                    data-location="{{ route('locality.select2',['country'=>$Country->CountryCode]) }}">{{ $Country->Flag}} {{ $Country->Name}}</option>
                                        @endforeach
                                    </select>
                                    <p id="Country_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <div class="col-sm-6 col-12 mb-3">
                                    <label for="Location" class="form-label">Location <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control locations" name="Location" id="Location"
                                            required disabled>
                                        <option selected
                                                value="{{ $competitor->LocationID }}">{{ $location }}</option>
                                    </select>
                                    <p id="Location_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <div class="col-sm-6 col-12 mb-3">
                                    <label class="form-label" for="Phone">Phone Number <span
                                            class="text-danger">*  &nbsp; <span id="PhonePrefix"></span></span></label>
                                    <input type="text" class="form-control" id="Phone" name="Phone"
                                           placeholder="Phone Number" required value="{{ $competitor->Phone }}">
                                    <p id="Phone_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                                </div>
                                <div class="col-sm-6 col-12 mb-3">
                                    <label class="form-label" for="CoreBusiness">Core Business <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="MarketShare" name="CoreBusiness"
                                           placeholder="Market Share" required value="{{ $competitor->CoreBusiness }}">
                                    <p id="CoreBusiness_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <div class="col-sm-6 col-12 mb-3">
                                    <label class="form-label" for="Clients">Clients Count <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="Clients" name="Clients"
                                           placeholder="Clients" required value="{{ $competitor->Clients }}">
                                    <p id="Clients_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                                </div>
                                <div class="col-sm-6 col-12 mb-3">
                                    <label class="form-label" for="MarketShare">Market Share <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="MarketShare" name="MarketShare"
                                           placeholder="Market Share" required value="{{ $competitor->MarketShare }}">
                                    <p id="MarketShare_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label" for="Notes">Notes </label>
                                    <textarea name="Notes" id="Notes" rows="3"
                                              class="form-control">{{ $competitor->Notes }}</textarea>
                                    <p id="Notes_end_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="updateCompetitorBtn" type="submit"><i
                                        class="fas fa-save"></i>
                                    update {{ \Illuminate\Support\Str::limit($competitor->CompetitorName,20) }}
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

    <script src="{{asset('assets/libs/jquery-form/jquery.form.min.js')}}"></script>
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script>
        const $Modal = $('#CompetitorActionsModal'),
            ItemsUrl = '{{ route('competitor-descriptions.index',[$competitor->CompetitorID]) }}';
        let competitorProductsTable = null, StrengthTable = null, WeaknessesTable = null, progressInterval = null
        CustomerServicePerceptionTable = null;
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';

            @if($hasProgress)
            fetchProgress();
            $(document).on('click', '.add-product-modal', function () {
                nWarning('You can\'t add products to this competitor, processing on going.');
            });
            $(document).on('click', '.modal-update-competitor', function () {
                nWarning('You can\'t update this competitor, processing on going.');
            });
            $(document).on('click', '.modal-trash-competitor', function () {
                nWarning('You can\'t delete this competitor, processing on going.');
            });
            @else
            $(document).on('click', '.modal-update-competitor', function () {
                $(".modal-title").html('Update Competitor: {{ $competitor->Name }}');
                $(".modal-item").addClass('d-none');
                $('#updateCompetitorModal').removeClass('d-none');
                $('.modal-dialog').addClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#updateCompetitorForm').submit(function (e) {
                e.preventDefault();
                const saveBtn = $('#updateCompetitorBtn');
                const btnContent = saveBtn.html();
                $(".form-control").removeClass('is-invalid');
                $('.error').addClass('d-none');
                saveBtn.prop('disable', true).addClass('disabled').prop('type', 'button').html('<i class="fas fa-spinner fa-spin"></i> please wait');
                $(this).ajaxSubmit({
                    dataType: 'json', beforeSubmit: function () {
                        $("#progress-bar").width('0%');
                    },
                    uploadProgress: function (event, position, total, percentComplete) {
                        $("#progress-bar").width(percentComplete + '%').html('<small id="progress-status">' + percentComplete + ' % Complete</small>');
                    },
                    success: function (data) {
                        $('.avatar-change').addClass('d-none');
                        $('.avatar-changed').removeClass('d-none');
                        nSuccess(data.message);
                        $("#progress-bar").width('0%').html('0');
                        window.setTimeout(function () {
                            window.location.replace(data.route);
                        }, 3000)
                        saveBtn.html('<i class="fas fa-check-double"></i> saved successfully.');
                        $Modal.modal('hide');
                    },
                    error: function (request) {
                        saveBtn.prop('disable', false).removeClass('disabled').prop('type', 'submit').html(btnContent);
                        formRequest(request, true)
                    }, resetForm: true
                });
                return false;
            });
            $(document).on('click', '.add-product-modal', function () {
                $(".modal-item").addClass('d-none');
                $('#CreateProductModal').removeClass('d-none');
                $('.modal-title').html('Add a product.');
                $('.modal-dialog').removeClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#CreateProductForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#CreateProductBtn'), false, true, true)) {
                    $Modal.modal('hide');
                    fetchCompetitorProducts();
                }
            });

            $(document).on('click', '#triggerFetchDataBtn', function () {
                $(".modal-item").addClass('d-none');
                $('#FetchDataModal').removeClass('d-none');
                $('.modal-title').html('Use LLM (AI) and crawler to fetch competitor data.');
                $('.modal-dialog').removeClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#FetchDataForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#FetchDataBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.modal-trash-competitor', function () {
                $(".modal-item").addClass('d-none');
                $('#trashCompetitorModal').removeClass('d-none');
                $('.modal-title').html('<b>DELETE</b> competitor - {{ $competitor->CompetitorName}}.');
                $('.modal-dialog').removeClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#trashCompetitorForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#trashCompetitorBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });
            @endif
            $('#Country').val('{{ $competitor->country->CountryCode }}').change().select2({
                placeholder: "Select a Country",
                dropdownParent: $Modal
            }).on('change', function () {
                const option = $(this).find('option:selected');
                $('#PhonePrefix').html(option.data('phone'));
                $('#Location').prop('disabled', false).select2('destroy').val(null).select2({
                    placeholder: "Search for the Location",
                    minimumInputLength: 2,
                    dropdownParent: $Modal,
                    ajax: {
                        url: option.data('location'),
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {q: $.trim(params.term)};
                        },
                        processResults: function (data) {
                            return {
                                results: $.map(data, function (item) {
                                    return {text: item.Name, id: item.ID}
                                })
                            };
                        },
                        cache: true
                    }
                });
            });
            $('#Location').select2();

            {{-- $('#Location').select2({
                placeholder: "Select a Town/City", minimumInputLength: 2,
                dropdownParent: $Modal,
                ajax: {
                    url: "{{ route('locality.select2') }}?type={{ LocalityTypeEnum::City->value }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {q: $.trim(params.term)};
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {text: item.Name, id: item.ID}
                            })
                        };
                    },
                    cache: true
                }
            }); --}}
            $("#Upload_image").change(function () {
                $('.avatar-change').removeClass('d-none');
                $('.avatar-changed').addClass('d-none');
                readURL(this);
            });


            $(document).on('click', '.trash-item-modal', function () {
                $(".modal-item").addClass('d-none');
                $('#deleteItemModal').removeClass('d-none');
                const stuff = $(this).data('info').split('~');
                $('.modal-title').html('Trash  ' + stuff[1]);
                $("#deleteItem").html(stuff[1]);
                $('#deleteItemForm').attr('action', ItemsUrl + '/' + stuff[0]);
                $('.modal-dialog').removeClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#deleteItemForm').submit(async function (e) {
                e.preventDefault();
                let response = await saveForm($(this), $('#deleteItemBtn'), false, true, true, true);
                if (response) {
                    $Modal.modal('hide');
                    $("#Fetch" + response.list).trigger("click");
                }
            });

            $(document).on('click', '.add-s_w-modal', function () {
                $(".modal-item").addClass('d-none');
                $('#CreateDescriptionModal').removeClass('d-none');
                const type = $(this).data('type');
                $('.modal-title').html('Add  ' + type);
                $('#CreateDescriptionForm').attr('action', ItemsUrl + '?_type=' + type);
                $('.modal-dialog').removeClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#CreateDescriptionForm').submit(async function (e) {
                e.preventDefault();
                let response = await saveForm($(this), $('#CreateDescriptionBtn'), false, true, true);
                if (response) {
                    $Modal.modal('hide');
                    $("#Fetch" + response.list).trigger("click");
                }
            });

            fetchCompetitorProducts();
        });

        @if($hasProgress)
        function fetchProgress() {
            if (progressInterval !== null) {
                clearInterval(progressInterval);
            }
            $.get("{{ route('competitor-fetch-online.index', [$competitor->CompetitorID]) }}", function (data) {
                $("#processingProgress").width(data.progress + '%').html('<small id="progress-status">' + data.description + '</small>');
                if (data.progress > 99) {
                    window.setTimeout(function () {
                        window.location.reload();
                    }, 3000)
                } else {
                    progressInterval = setInterval(function () {
                        fetchProgress();
                    }, 5000);
                }
            });
        }
        @endif


        function fetchSnW() {
            fetchCompetitorStrengths();
            fetchCompetitorWeaknesses();
        }

        function fetchCustomerServicePerception() {
            if (CustomerServicePerceptionTable === null) {
                CustomerServicePerceptionTable = fetchItems('CustomerServicePerception');
            } else {
                CustomerServicePerceptionTable.ajax.reload();
            }
        }

        function fetchCompetitorStrengths() {
            if (StrengthTable === null) {
                StrengthTable = fetchItems('Strength');
            } else {
                StrengthTable.ajax.reload();
            }
        }

        function fetchCompetitorWeaknesses() {
            if (WeaknessesTable === null) {
                WeaknessesTable = fetchItems('Weaknesses');
            } else {
                WeaknessesTable.ajax.reload();
            }
        }


        function fetchCompetitorProducts() {
            if (competitorProductsTable === null) {
                competitorProductsTable = $('#competitorProductsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[3, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: '{{ route('competitor-products.index',[$competitor->CompetitorID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {
                            data: {
                                _: "DT_RowIndex",
                                sort: "Id",
                            }, name: 'Id', searchable: false
                        },
                        {data: 'Name', name: 'Name'},
                        {data: 'Limit', name: 'Limit'},
                        {data: 'InterestRate', name: 'InterestRate'},
                        {data: 'Clients', name: 'Clients'},
                    ], "oLanguage": {
                        "sEmptyTable": "No products found under this filter <a href='#' class='add-product-modal'>add a new one.</a>"
                    }
                });

                competitorProductsTable.on('error', function () {
                    nWarning("an issue occurred while loading the competitor products.");
                    //console.log(er);
                });
            } else {
                competitorProductsTable.ajax.reload();
            }
        }


        function fetchItems(type) {
            let table = $('#' + type + 'Table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: ItemsUrl + '?_type=' + type,
                    error: function (jqXHR) {
                        codeNotify(jqXHR.status);
                    }
                },
                columns: [
                    {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                    {data: 'Description', name: 'Description'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ], "oLanguage": {
                    "sEmptyTable": "No records found"
                }
            });

            table.on('error', function () {
                nWarning("an issue occurred while loading the list.");
            });
            return table;
        }

        function readURL(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#image_upload_preview').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

    </script>
@endsection
