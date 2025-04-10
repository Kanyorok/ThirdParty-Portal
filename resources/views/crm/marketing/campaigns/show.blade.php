@php use Illuminate\Support\Str; @endphp
@extends('layouts.app')

@section('title')
    Campaign {{ Str::limit($campaign->Label,50) }}
@endsection
@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.css"
          integrity="sha512-ngQ4IGzHQ3s/Hh8kMyG4FC74wzitukRMIcTOoKT3EyzFZCILOPF0twiXOQn75eDINUfKBYmzYn2AA8DkAk8veQ=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-4 col-xxl-3">
            <div class="card">
                <div class="card-body">
                    <h2 class="text-center">{{ $campaign->Label }} </h2>
                    <p class="text-center">{{ $campaign->Type->name }}</p>
                    <p class="text-center"><b>Status: </b> {{ $campaign->Status->name }}</p>
                    <p class="text-center">Contacts: <b>{{ number_format($campaign->contacts()->count()) }}</b></p>
                    <p class="text-center">{{ $campaign->Notes }}</p>

                    @include('snippets.behind_scenes',['model'=>$campaign])

                    @if( $campaign->Status->value === \App\Enums\CampaignStatusEnum::Draft->value)
                        @if(!$campaign->Processing)
                            <hr>
                            <button type="button" class="btn btn-success campaign-submit m-2 w-100">
                                <i class="fas fa-plane-departure"></i> submit for approval
                            </button>
                            <button class="btn btn-danger w-100 m-2 cancel-campaign-action"><i
                                    class="fas fa-trash"></i> Cancel Campaign
                            </button>
                        @else
                            <h3 class="text-center py-2">Processing Contacts</h3>
                        @endif
                    @elseif($campaign->Status->value === \App\Enums\CampaignStatusEnum::Approval->value)
                        @if($canApprove)
                            <button type="button" class="btn btn-success campaign-approve m-2 w-100">
                                <i class="fas fa-check"></i> approve
                            </button>
                            <button type="button" class="btn btn-danger campaign-reject m-2 w-100">
                                <i class="fas fa-times"></i> reject
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-8 col-xxl-9">
            <div class="tab">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" href="#tab-1" data-bs-toggle="tab" role="tab"
                                            aria-selected="false">Content</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tab-0" data-bs-toggle="tab" role="tab"
                                            aria-selected="false" onclick="fetchCampaignContactsTable()">Contacts </a>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="#tab-2" data-bs-toggle="tab" role="tab"
                                            aria-selected="false" onclick="fetchWorkflowTable()">Workflow</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane  active m-2" id="tab-1" role="tabpanel">
                        @if($hasProgress)
                            <div class="progress mb-3" style="height: 20px;">
                                <div id="campaignProgress"
                                     class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                     style="width: 0" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        @endif
                        @if($campaign->Status->value === \App\Enums\CampaignStatusEnum::Draft->value)
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
                                            <ul class="campaign-group campaign-group-flush">
                                                <li class="campaign-group-item">You can use <code> #name</code> to be
                                                    replaced by
                                                    their name while sending.
                                                </li>
                                                <li class="campaign-group-item">You can use <code> #date</code> to be
                                                    replaced by
                                                    {{ \Carbon\Carbon::now()->format('M d, Y') }} while sending.
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <form action="{{ route('campaigns.update',[$campaign->CampaignID]) }}" method="post"
                                  id="updateCampaignForm">@method('put')
                                @csrf
                                @if($campaign->Type->value === \App\Enums\CampaignTypeEnum::Email->value)
                                    <div class="mb-3">
                                        <label class="form-label" for="Subject">Subject <span
                                                class="text-danger">*</span> <small>same as campaign
                                                label</small></label>
                                        <input type="text" class="form-control" id="Subject" name="Subject" required
                                               value="{{ $campaign->Label }}">
                                        <p id="Subject_error" class="invalid-feedback d-none error col-12"
                                           role="alert"></p>
                                    </div>
                                    <div class="mb-3 col-12">
                                        <label class="form-label" for="Content">Content <span
                                                class="text-danger">*</span></label> &nbsp;
                                        <span id="Content_error" class="invalid-feedback d-none error col-12"
                                              role="alert"></span>
                                        <textarea name="Content" id="Content" class="form-control" rows="4"
                                                  maxlength="50000" minlength="2">{!! $campaign->Details !!}</textarea>
                                    </div>
                                @elseif($campaign->Type->value === \App\Enums\CampaignTypeEnum::SMS->value)
                                    <div class="mb-3 col-12">
                                        <label class="form-label" for="Content">Content <span
                                                class="text-danger">*</span></label>
                                        <b class="float-end text-info" id="msgCounter"></b>
                                        <textarea name="Content" id="Content" class="form-control" rows="4"
                                                  maxlength="50000" minlength="2">{!! $campaign->Details !!}</textarea>
                                        <p id="Content_error" class="invalid-feedback d-none error col-12"
                                           role="alert"></p>
                                    </div>
                                @endif
                                <hr>
                                <button type="submit"
                                        class="float-end btn btn-outline-primary w-50 "
                                        id="updateCampaignBtn"><i class="fa fa-save"></i> update Content
                                </button>
                                <div class="clearfix"></div>
                            </form>
                        @else
                            <h3>Status: <b class="text-success">{{ $campaign->Status->name }}</b></h3>
                            {!! $campaign->Details !!}
                        @endif
                    </div>
                    <div class="tab-pane m-2" id="tab-0" role="tabpanel">
                        <table id="campaignContactsTable"
                               class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Party</th>
                                <th>Status</th>
                                <th>Dated</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="tab-pane m-2" id="tab-2" role="tabpanel">
                        <table id="campaignWorkflowTable"
                               class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                            <thead>
                            <tr>
                                <th>No.</th>
                                <th>Stage</th>
                                <th>Status</th>
                                <th>Dated</th>
                                <th>By</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="campaignActionsModel" tabindex="-1" role="dialog" aria-hidden="true"
         data-bs-backdrop="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($campaign->Status->value === \App\Enums\CampaignStatusEnum::Draft->value)
                        <div class="onboarding-content text-center with-gradient d-none modal-item"
                             id="cancelCampaignModal">
                            <p class="text-danger h4">
                                Cancel Campaign <b>{{ $campaign->Label }}</b>
                            </p>
                            <div class="mt-2 mb-2">
                                Are you sure you want to cancel this campaign ?
                            </div>
                            <hr>
                            <form id="cancelCampaignForm"
                                  action="{{ route('campaigns.destroy',[$campaign->CampaignID])  }}"
                                  method="post"> @csrf
                                <div class="mt-4">@method('delete')
                                    <button type="button" class="btn btn-success float-start"
                                            data-bs-dismiss="modal">
                                        no, keep
                                    </button>
                                    <button class="btn btn-danger float-end" id="cancelCampaignBtn" type="submit"><i
                                            class="fas fa-trash"></i> yes, cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="onboarding-content with-gradient d-none modal-item text-center"
                             id="submitCampaignModal">
                            <h4 class="text-success">
                                Submit Campaign <b>{{ $campaign->Label }}</b> for Approval?
                            </h4>
                            <p class="text-muted">This action is non reversible, are you sure ?</p>
                            <form id="submitCampaignForm" method="post"
                                  action="{{ route('campaigns.submit',[$campaign->CampaignID]) }}"> @csrf @method('put')
                                <div class="mt-4">
                                    <button type="button" class="btn btn-secondary float-start"
                                            data-bs-dismiss="modal">
                                        no, cancel
                                    </button>
                                    <button class="btn btn-success float-end" id="submitCampaignBtn"
                                            type="submit"><i
                                            class="fas fa-check"></i> yes, submit
                                    </button>
                                </div>
                            </form>
                        </div>
                    @elseif($campaign->Status->value === \App\Enums\CampaignStatusEnum::Approval->value && $canApprove)
                        <div class="onboarding-content with-gradient d-none modal-item text-center"
                             id="approveCampaignModal">
                            <h4 class="text-success">
                                Approve Campaign <b>{{ $campaign->Label }}</b>
                            </h4>
                            <div class="alert alert-primary" role="alert">
                                <div class="alert-icon">
                                    <i class="far fa-fw fa-bell"></i>
                                </div>
                                <div class="alert-message">
                                    <strong>Note!</strong> This being the last step of approval, means this campaign
                                    with run afterwards.
                                </div>
                            </div>
                            <p class="text-muted">This action is non reversible, are you sure ?</p>
                            <form id="approveCampaignForm" method="post"
                                  action="{{ route('approve-campaigns.update',[$campaign->CampaignID]) }}"> @csrf @method('put')
                                <div class="mt-4">
                                    <button type="button" class="btn btn-secondary float-start"
                                            data-bs-dismiss="modal">
                                        no, cancel
                                    </button>
                                    <button class="btn btn-success float-end" id="approveCampaignBtn"
                                            type="submit"><i
                                            class="fas fa-check"></i> yes, submit
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="onboarding-content with-gradient d-none modal-item text-center"
                             id="rejectCampaignModal">
                            <h4 class="text-danger">
                                Reject Campaign <b>{{ $campaign->Label }}</b>
                            </h4>
                            <p class="text-muted">This plan with be reverted to owner for update using the notes
                                given ?</p>
                            <form id="rejectCampaignForm" method="post"
                                  action="{{ route('approve-campaigns.destroy',[$campaign->CampaignID]) }}"> @csrf @method('delete')
                                <div class="mb-3 text-start">
                                    <label class="form-label" for="campaign_reject_reason">Reject Reason
                                        <span class="text-danger">*</span></label>
                                    <textarea name="campaign_reject_reason" id="campaign_reject_reason"
                                              class="form-control" rows="4" required
                                              maxlength="5000"></textarea>
                                    <p id="campaign_reject_reason_error"
                                       class="invalid-feedback d-none error col-12" role="alert"></p>
                                </div>
                                <div class="mt-4">
                                    <button type="button" class="btn btn-secondary float-start"
                                            data-bs-dismiss="modal">
                                        no, cancel
                                    </button>
                                    <button class="btn btn-danger float-end" id="rejectCampaignBtn"
                                            type="submit"><i
                                            class="fas fa-times"></i> yes, reject
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.js"
            integrity="sha512-6F1RVfnxCprKJmfulcxxym1Dar5FsT/V2jiEUvABiaEiFWoQ8yHvqRM/Slf0qJKiwin6IDQucjXuolCfCKnaJQ=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('assets/js/datatables.js') }}"></script>
    <script>
        const maxLimit = 168, $Modal = $('#campaignActionsModel');
        let smsCount = 1, campaignContactsTable = null, campaignWorkflowTable = null, progressInterval = null;

        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchCampaignContactsTable();

            @if($hasProgress)
            fetchProgress();
            @endif

            @if( $campaign->Status->value === \App\Enums\CampaignStatusEnum::Draft->value)
            $(document).on('click', '.campaign-submit', function () {
                $(".modal-title").html('Submit campaign for approval');
                $(".modal-item").addClass('d-none');
                $('#submitCampaignModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#submitCampaignForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#submitCampaignBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.cancel-campaign-action', function () {
                $(".modal-title").html('<b class="text-danger">Cancel</b> Campaign');
                $(".modal-item").addClass('d-none');
                $('#cancelCampaignModal').removeClass('d-none');
                $Modal.modal('show');
            });

            $('form#updateCampaignForm').submit(async function (e) {
                e.preventDefault();
                await saveForm($(this), $('#updateCampaignBtn'), false, false, true);
            });

            $('form#cancelCampaignForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#cancelCampaignBtn'), true, true, true)) {
                    $Modal.modal('hide');

                }
            });

            @if($campaign->Type->value === \App\Enums\CampaignTypeEnum::Email->value)
            $('textarea#Content').summernote({
                placeholder: '',
                dialogsInBody: true,
                height: 300,
                tabsize: 2,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture' /*,'video'*/]],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
            @elseif($campaign->Type->value === \App\Enums\CampaignTypeEnum::SMS->value)
            $('#Content').keyup(function () {
                $('#msgCounter').html(parseInt((this.value.length / window.smsMaxLimit) + 1) + " sms's");
            });
            @endif
            @elseif($campaign->Status->value === \App\Enums\CampaignStatusEnum::Approval->value && $canApprove)
            $(document).on('click', '.campaign-approve', function () {
                $(".modal-title").html('<b class="text-success">APPROVE</b> campaign');
                $(".modal-item").addClass('d-none');
                $('#approveCampaignModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#approveCampaignForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#approveCampaignBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });
            $(document).on('click', '.campaign-reject', function () {
                $(".modal-title").html('<b class="text-danger">REJECT</b> campaign');
                $(".modal-item").addClass('d-none');
                $('#rejectCampaignModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#rejectCampaignForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#rejectCampaignBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });
            @endif
        });

        @if($hasProgress)
        function fetchProgress() {
            if (progressInterval !== null) {
                clearInterval(progressInterval);
            }
            $.get("{{ route('campaigns.progress', [$campaign->CampaignID]) }}", function (data) {
                $("#campaignProgress").width(data.progress + '%').html('<small id="progress-status">' + data.description + '</small>');
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

        function fetchWorkflowTable() {
            if (campaignWorkflowTable === null) {
                campaignWorkflowTable = $('#campaignWorkflowTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[3, 'asc']],
                    /*"columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    */
                    ajax: {
                        url: '{{ route('campaigns.workflow',[$campaign->CampaignID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'Status', name: 'Status'},
                        {data: 'Stage', name: 'Stage'},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                        {data: 'creator.Name', name: 'creator.Name'},
                    ], "oLanguage": {
                        "sEmptyTable": "no workflow under this filter"
                    }
                });

                campaignWorkflowTable.on('error', function (er) {
                    nWarning("an issue occurred while loading workflow.");
                    console.log(er);
                });
            } else {
                campaignWorkflowTable.ajax.reload();
            }
        }

        function fetchCampaignContactsTable() {
            if (campaignContactsTable === null) {
                campaignContactsTable = $('#campaignContactsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[3, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: '{{ route('campaigns.contacts',[$campaign->CampaignID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'party', name: 'party'},
                        {data: 'Status', name: 'Status'},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                    ], "oLanguage": {
                        "sEmptyTable": "no contacts found here"
                    }
                });

                campaignContactsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading contacts.");
                    console.log(er);
                });
            } else {
                campaignContactsTable.ajax.reload();
            }
        }
    </script>
@endsection
