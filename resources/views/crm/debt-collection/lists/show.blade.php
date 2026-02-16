@php use Illuminate\Support\Str; @endphp
@extends('layouts.app')

@section('title')
    {{ Str::limit($list->Label,50) }} List
@endsection
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/2.0.5/css/select.dataTables.css">
@endsection
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">CRM</a></li>
    <li class="breadcrumb-item"><a href="{{ route('loans-list.index') }}">Loans List</a></li>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body border-bottom border-1">
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <h2 class="float-start">{!! $list->Visibility->icon() !!} <a
                                    href="{{ route('loans-list.show',$list->slug) }}"
                                    class="text-black text-decoration-underline">{{ $list->Label }}</a></h2>
                        </div>
                        @can('update',$list)
                            <div class="col-md-6 col-12">
                                <button type="button" class="btn btn-primary float-end mx-2 modal-create-campaign">
                                    <i class="fas fa-square-envelope"></i> send message
                                </button>

                                <a href="{{ route('loans-list.edit',[$list->slug]) }}"
                                   class="btn btn-info  float-end mx-2"><i
                                        class="fas fa-edit"></i> update list</a>

                            </div>
                        @endcan
                        <div class="col-12">
                            <p class="mb-0"><b class="me-2">Loans List </b> <span class="mx-2">|</span>Contacts:
                                <b>{{ number_format($contacts_count) }}</b> <span class="mx-2">|</span> <b>Last
                                    Contacted: </b> {{ ($list->LastContacted)?$list->LastContacted->diffForHumans():'Never'  }}
                                <span class="mx-2">|</span> {{ $list->Notes }}</p>
                        </div>
                    </div>
                </div>
                <details class="card-footer">
                    <summary>Other Details</summary>
                    <div class="row">
                        <div class="col-md-6 col-12 text-muted">
                            Creation <span class="ms-2">{{ $list->CreatedOn?->format('d M, Y H:i') }} : {{ $list->creator?->UserID }} - {{ $list->creator?->Name }}</span>
                        </div>
                        <div class="col-md-6 col-12 text-muted">
                            Modified <span class="ms-2">{{ $list->ModifiedOn?->format('d M, Y H:i') }} : {{ $list->modified?->UserID }} - {{ $list->modified?->Name }}</span>
                        </div>
                    </div>
                </details>
            </div>
        </div>
        <div class="col-md-12">
            <div class="tab">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" href="#loansTab" data-bs-toggle="tab" role="tab"
                                            onclick="fetchProductsTable()">Loans </a></li>
                    <li class="nav-item"><a class="nav-link" href="#campaignsTab" data-bs-toggle="tab" role="tab"
                                            onclick="fetchCampaignsTable()">Notifications</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="loansTab" role="tabpanel">
                        <div class="table-responsive">
                            <table id="productsTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100">
                                <thead>
                                <tr>
                                    <th>AccountId</th>
                                    <th>Client</th>
                                    <th>Status</th>
                                    <th>Balance</th>
                                    <th>Arrears Days</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane" id="campaignsTab" role="tabpanel">
                        <div class="table-responsive">
                            <table id="campaignTable"
                               class="table table-striped dataTable no-footer dtr-inline w-100">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Label</th>
                                <th>Status</th>
                                <th>Contacts</th>
                                <th>Dated</th>
                                <th>Actions</th>
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

    <div class="modal fade" id="LoansActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item" id="createLoansCampaignModal">
                        @include('crm.debt-collection.help.communication')
                        <form action="{{ route('loans-campaigns.store',[$list->slug]) }}" method="post"
                              id="createLoansCampaignForm">
                            <div class="mb-3"> @csrf
                                <label class="form-label" for="Label">Label <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="Label" name="Label" required
                                       placeholder="Label">
                                <p id="Label_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3 col-12">
                                <label class="form-label" for="Content">
                                    Content <span class="text-danger">*</span>
                                </label> &nbsp;<b class="float-end text-info" id="msgCounter"></b>
                                <span id="Content_error" class="invalid-feedback d-none error col-12"
                                      role="alert"></span>
                                <textarea name="Content" id="Content" class="form-control" rows="4"
                                          maxlength="50000" minlength="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Notes">Notes </label>
                                <textarea name="Notes" id="Notes" rows="3" class="form-control"
                                          maxlength="1000"></textarea>
                                <p id="Notes_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="createLoansCampaignBtn" type="submit">
                                    <i class="align-middle" data-feather="send"></i> send campaign
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

    <script>const $Modal = $('#LoansActionsModal');
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchProductsTable();

            $(document).on('click', '.modal-create-campaign', function () {
                $(".modal-title").html('Send Bulk SMS Notification to Loanee');
                $(".modal-item").addClass('d-none');
                $('#createLoansCampaignModal').removeClass('d-none');
                $Modal.modal('show');
            });

            $('form#createLoansCampaignForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#createLoansCampaignBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });
            $('#Content').keyup(function () {
                $('#msgCounter').html(parseInt((this.value.length / window.smsMaxLimit) + 1) + " sms's");
            });
        });

        function fetchProductsTable() {
            if (!$.fn.DataTable.isDataTable('#productsTable')) {
                $('#productsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    // "order": [[3, 'asc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: '{!! route('loans-list.accounts', [$list->slug,'q'=>'current', 'dated'=>($dated instanceof Carbon\Carbon)? $dated->format('U'):0]) !!}',
                        error: function (request) {
                            if (request.status === 400 && request.responseJSON.message) {
                                nWarning(request.responseJSON.message);
                            } else {
                                codeNotify(request.status);
                            }
                        }
                    },
                    columns: [
                        {data: 'AccountID', name: 'AccountID'},
                        {data: 'ClientID', name: 'ClientID'},
                        {data: 'Classification', name: 'Classification'},
                        {data: 'OutstandingBalance', name: 'OutstandingBalance'},
                        {data: 'ArrearsDays', name: 'ArrearsDays'},
                    ], "oLanguage": {
                        "sEmptyTable": "no loans found here."
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading loans lists.");
                });
            } else {
                $('#productsTable').DataTable().ajax.reload();
            }
        }

        function fetchCampaignsTable() {
            if (!$.fn.DataTable.isDataTable('#campaignTable')) {
                $('#campaignTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    // "order": [[3, 'asc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: '{{ route('loans-campaigns.index',[$list->slug]) }}',
                        error: function (request) {
                            if (request.status === 400 && request.responseJSON.message) {
                                nWarning(request.responseJSON.message);
                            } else {
                                codeNotify(request.status);
                            }
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'Label', name: 'Label'},
                        {data: 'Status', name: 'Status'},
                        {data: 'contacts_count', name: 'contacts_count'},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no campaigns under this filter"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading campaigns.");
                });
            } else {
                $('#campaignTable').DataTable().ajax.reload();
            }
        }
    </script>
@endsection
