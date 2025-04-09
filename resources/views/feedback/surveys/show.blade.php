@extends('layouts.app')

@section('title')
    Survey {{ $survey->SurveyID }}
@endsection
@section('styles')

@endsection
@section('content')
    <div class="row">
        <div class="col-md-4 col-xxl-3">
            <div class="card">
                <div class="card-body">
                    <h2 class="text-center">{{ $survey->SurveyID }} </h2>
                    <h3 class="text-center">{{ $survey->Label }} </h3>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">Status: <b class="float-end">{{ $survey->Status->name }}</b></li>
                        <li class="list-group-item">Start: <span
                                class="float-end">{{ $survey->StartOn?->format('M d, Y') }}</span></li>
                        <li class="list-group-item">End: <span
                                class="float-end">{{ $survey->EndOn?->format('M d, Y') }}</span></li>
                    </ul>
                    <p class="text-center">{{ $survey->Notes }}</p>
                    @include('snippets.behind_scenes',['model'=>$survey])

                    @if( $survey->Status->value === \App\Enums\Feedback\SurveyStatusEnum::Draft->value)
                        <hr>
                        <a href="{{ route('surveys.edit',[$survey->SurveyID]) }}" class="btn btn-info w-100">
                            <i class="fas fa-edit"></i> update survey
                        </a>
                    @elseif($survey->Status->value === \App\Enums\Feedback\SurveyStatusEnum::Approval->value)
                        @if($canApprove)
                            <hr>
                            <button type="button" class="btn btn-success survey-approve m-2 w-100">
                                <i class="fas fa-check"></i> approve
                            </button>
                            <button type="button" class="btn btn-danger survey-reject m-2 w-100">
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
                    <li class="nav-item"><a class="nav-link" href="#tab-2" data-bs-toggle="tab" role="tab"
                                            aria-selected="false" onclick="fetchWorkflowTable()">Workflow</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane  active m-2" id="tab-1" role="tabpanel">
                        @forelse ($questions as $question)
                            <div class="border border-1 my-2 rounded">
                                <h4 class="m-1 border-bottom"><b>{{ $loop->iteration }}. </b>{{ $question->Question }}
                                    @if(!empty($question->Notes))
                                        <span class="text-muted fs-6">({{ $question->Notes }})</span>
                                    @endif</h4>
                                @if($question->Type->value === \App\Enums\Feedback\SurveyQuestionTypeEnum::Closed->value)
                                    @if($question->answers->isEmpty())
                                        <div class="mx-2 mt-2">
                                            <div class="alert alert-primary" role="alert">
                                                <div class="alert-message">
                                                    <strong>No Options</strong> this question does not have options.
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <ol>
                                            @foreach($question->answers as $answer)
                                                <li>{{ $answer->Answer }}</li>
                                            @endforeach
                                        </ol>
                                    @endif
                                @elseif($question->Type->value === \App\Enums\Feedback\SurveyQuestionTypeEnum::Open->value)
                                    <p class="m-2">Open ended question.</p>
                                @endif
                            </div>
                        @empty
                            <div class="my-3 mx-1">
                                <div class="alert alert-primary" role="alert">
                                    <div class="alert-icon">
                                        <i class="far fa-fw fa-bell"></i>
                                    </div>
                                    <div class="alert-message">
                                        <strong>Add Questions</strong> Add questions to this survey!
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <a href="{{ route('surveys.edit',[$survey->SurveyID]) }}"
                                       class="btn btn-info w-100">
                                        <i class="fas fa-edit"></i> add questions
                                    </a>
                                </div>
                            </div>
                        @endforelse
                    </div>
                    <div class="tab-pane m-2" id="tab-2" role="tabpanel">
                        <table id="surveyWorkflowTable"
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

    <div class="modal fade" id="surveyActionsModel" tabindex="-1" role="dialog" aria-hidden="true"
         data-bs-backdrop="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($survey->Status->value === \App\Enums\Feedback\SurveyStatusEnum::Approval->value && $canApprove)
                        <div class="onboarding-content with-gradient d-none modal-item text-center"
                             id="approveSurveyModal">
                            <h4 class="text-success">
                                Approve Survey <b>{{ $survey->Label }}</b>
                            </h4>
                            <div class="alert alert-primary" role="alert">
                                <div class="alert-icon">
                                    <i class="far fa-fw fa-bell"></i>
                                </div>
                                <div class="alert-message">
                                    <strong>Note!</strong> This being the last step of approval, means this survey
                                    with run afterwards.
                                </div>
                            </div>
                            <p class="text-muted">This action is non reversible, are you sure ?</p>
                            <form id="approveSurveyForm" method="post"
                                  action="{{ route('approve-surveys.update',[$survey->SurveyID]) }}"> @csrf @method('put')
                                <div class="mt-4">
                                    <button type="button" class="btn btn-secondary float-start"
                                            data-bs-dismiss="modal">
                                        no, cancel
                                    </button>
                                    <button class="btn btn-success float-end" id="approveSurveyBtn"
                                            type="submit"><i
                                            class="fas fa-check"></i> yes, submit
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="onboarding-content with-gradient d-none modal-item text-center"
                             id="rejectSurveyModal">
                            <h4 class="text-danger">
                                Reject Survey <b>{{ $survey->Label }}</b>
                            </h4>
                            <p class="text-muted">This plan with be reverted to creator for update using the notes
                                given ?</p>
                            <form id="rejectSurveyForm" method="post"
                                  action="{{ route('approve-surveys.destroy',[$survey->SurveyID]) }}"> @csrf @method('delete')
                                <div class="mb-3 text-start">
                                    <label class="form-label" for="survey_reject_reason">Reject Reason
                                        <span class="text-danger">*</span></label>
                                    <textarea name="survey_reject_reason" id="survey_reject_reason"
                                              class="form-control" rows="4" required
                                              maxlength="5000"></textarea>
                                    <p id="survey_reject_reason_error"
                                       class="invalid-feedback d-none error col-12" role="alert"></p>
                                </div>
                                <div class="mt-4">
                                    <button type="button" class="btn btn-secondary float-start"
                                            data-bs-dismiss="modal">
                                        no, cancel
                                    </button>
                                    <button class="btn btn-danger float-end" id="rejectSurveyBtn"
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
    <script src="{{ asset('assets/js/datatables.js') }}"></script>
    <script>
        const $Modal = $('#surveyActionsModel');
        let surveyWorkflowTable = null;

        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            @if($survey->Status->value === \App\Enums\Feedback\SurveyStatusEnum::Approval->value && $canApprove)
            $(document).on('click', '.survey-approve', function () {
                $(".modal-title").html('<b class="text-success">APPROVE</b> Survey');
                $(".modal-item").addClass('d-none');
                $('#approveSurveyModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#approveSurveyForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#approveSurveyBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });
            $(document).on('click', '.survey-reject', function () {
                $(".modal-title").html('<b class="text-danger">REJECT</b> survey');
                $(".modal-item").addClass('d-none');
                $('#rejectSurveyModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#rejectSurveyForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#rejectSurveyBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });
            @endif
        });


        function fetchWorkflowTable() {
            if (surveyWorkflowTable === null) {
                surveyWorkflowTable = $('#surveyWorkflowTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[3, 'asc']],
                    /*"columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    */
                    ajax: {
                        url: '{{ route('surveys.workflows',[$survey->SurveyID]) }}',
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

                surveyWorkflowTable.on('error', function (er) {
                    nWarning("an issue occurred while loading workflow.");
                    console.log(er);
                });
            } else {
                surveyWorkflowTable.ajax.reload();
            }
        }
    </script>
@endsection
