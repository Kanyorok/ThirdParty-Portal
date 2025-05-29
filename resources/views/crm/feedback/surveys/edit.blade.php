@extends('layouts.app')

@section('title')
    Survey {{ $survey->SurveyID }}
@endsection
@section('styles')

@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-sm-6 col-12">
                            <div class="mb-0">
                                <h3 class="mb-0 h3">
                                    <a href="{{ route('surveys.show',[$survey->SurveyID]) }}"
                                       class="text-black text-decoration-underline"><b>{{ $survey->SurveyID }}</b>
                                        : {{ $survey->Label }}</a>
                                </h3>
                                <div class="card-actions float-end d-block d-sm-none">
                                    <div class="dropdown position-relative">
                                        <a href="javascript: void(0)" data-bs-toggle="dropdown"
                                           data-bs-display="static">
                                            <i class="align-middle" data-feather="more-vertical"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item survey-add-question" href="javascript: void(0)"> Add
                                                question</a>
                                            <a class="dropdown-item survey-submit-approval" href="javascript: void(0)">Submit
                                                for
                                                Approval</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item modal-update-survey" href="javascript: void(0)">
                                                Update
                                                survey</a>
                                            <a class="dropdown-item modal-trash-survey " href="javascript: void(0)">
                                                Remove
                                                survey</a>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="pt-1">
                                <p><b class="me-2">Start : </b> {{ $survey->StartOn?->format('M d, Y') }} |
                                    <b class="me-2">End : </b> {{ $survey->EndOn?->format('M d, Y') }} </p>
                                <p class="justify-content-around">{{ $survey->Notes }}</p>
                            </div>

                        </div>
                        <div class="col-sm-6  d-none d-sm-block ">
                            <div class="float-end">
                                <button class="btn btn-primary my-1 survey-add-question" type="button"> Add
                                    question
                                </button>
                                <button class="btn btn-success my-1 survey-submit-approval" type="button">Submit for
                                    Approval
                                </button>
                                <button class="btn btn-info my-1 modal-update-survey" type="button"> Update
                                    survey
                                </button>
                                <button class="btn btn-danger my-1 modal-trash-survey " type="button"> Remove
                                    survey
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        @forelse ($questions as $question)
            <div class="col-12" id="{{ $question->SurveyQuestionId }}">
                <div class="card">
                    <div class="card-header">
                        <div class="card-actions float-end">
                            <div class="dropdown position-relative">
                                <a href="javascript: void(0)" data-bs-toggle="dropdown" data-bs-display="static">
                                    <i class="align-middle" data-feather="more-horizontal"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    @if($question->Type->value === \App\Enums\Feedback\SurveyQuestionTypeEnum::Closed->value)
                                        <a class="dropdown-item add-question-option" href="javascript: void(0)"
                                           data-info="{{ route('survey-question-answer.store',[$question->SurveyQuestionId]) }}~{{ $question->Question }}">
                                            Add option</a>
                                        <a class="dropdown-item add-question-option-5" href="javascript: void(0)"
                                           data-info="{{ route('survey-question-answer.five',[$question->SurveyQuestionId]) }}~{{ $question->Question }}">
                                            Add 1 -5 options</a>
                                        <a class="dropdown-item add-question-option-2" href="javascript: void(0)"
                                           data-info="{{ route('survey-question-answer.boolean',[$question->SurveyQuestionId]) }}~{{ $question->Question }}">
                                            Add Yes, No options</a>
                                    @else
                                        <a class="dropdown-item disabled text-decoration-line-through"
                                           href="javascript: void(0)">
                                            Add option</a>
                                    @endif
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item data-update-question" href="javascript: void(0)"
                                       data-info="{{ route('survey-question.update',[$survey->SurveyID, $question->SurveyQuestionId]) }}~{{ $question->Question }}~{{ $question->Notes }}">
                                        Update Question</a>
                                    <a class="dropdown-item data-remove-question" href="javascript: void(0)"
                                       data-info="{{ route('survey-question.destroy',[$survey->SurveyID, $question->SurveyQuestionId]) }}~{{ $question->Question }}">Remove
                                        Question</a>
                                </div>
                            </div>
                        </div>
                        <h5 class="card-title mb-0"><b>{{ $loop->iteration }}. </b>{{ $question->Question }}
                            @if(!empty($question->Notes))
                                <span class="text-muted fs-6">({{ $question->Notes }})</span>
                            @endif</h5>
                    </div>
                    <div class="card-body pt-1 border-top">
                        @if($question->Type->value === \App\Enums\Feedback\SurveyQuestionTypeEnum::Closed->value)
                            <table id="{{ $question->SurveyQuestionId }}AnswersTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Option</th>
                                    <th>action</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        @else
                            Open Ended Question No Options
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="my-3 mx-1">
                            <div class="alert alert-primary" role="alert">
                                <div class="alert-icon">
                                    <i class="far fa-fw fa-bell"></i>
                                </div>
                                <div class="alert-message">
                                    <strong>No Questions</strong> No question added to this survey add some.
                                </div>
                            </div>
                            <div class="mt-2 text-center">
                                <button class="btn btn-primary my-1 survey-add-question" type="button"> Add
                                    question
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
    <div class="modal fade" id="SurveyActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item" id="updateSurveyModal">
                        <form action="{{ route('surveys.update',[$survey->SurveyID]) }}" method="post"
                              id="updateSurveyForm"> @csrf @method('put')
                            <div class="mb-3">
                                <label class="form-label" for="Label">Label <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="Label" name="Label" required
                                       placeholder="Label" value="{{ $survey->Label }}">
                                <p id="Label_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Start">Start <span
                                            class="text-danger">*</span></label>
                                <input type="text" class="form-control flatpickr-datetime" id="Start"
                                       name="Start" placeholder="Select start."
                                       value="{{ $survey->StartOn?->format('Y-m-d') }}">
                                <p id="Start_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="End">End <span
                                            class="text-danger">*</span></label>
                                <input type="text" class="form-control flatpickr-datetime " id="End"
                                       name="End" placeholder="Select end."
                                       value="{{ $survey->EndOn?->format('Y-m-d') }}">
                                <p id="End_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Notes">Notes </label>
                                <textarea name="Notes" id="Notes" rows="3" class="form-control"
                                          maxlength="1000">{{ $survey->Notes }}</textarea>
                                <p id="Notes_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="updateSurveyBtn" type="submit"><i
                                            class="fas fa-save"></i>
                                    update {{ $survey->SurveyID }}
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item text-center" id="trashSurveyModal">
                        <h4 class="text-danger">
                            Trash Survey <b>{{ $survey->Label }}</b> ?
                        </h4>
                        <form id="trashSurveyForm" method="post"
                              action="{{ route('surveys.destroy',[$survey->SurveyID]) }}"> @csrf @method('delete')
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    no, cancel
                                </button>
                                <button class="btn btn-danger float-end" id="trashSurveyBtn"
                                        type="submit"><i
                                            class="fas fa-trash"></i> yes, trash
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item" id="addSurveyQuestionModal">
                        <form action="{{ route('survey-question.store',[$survey->SurveyID]) }}" method="post"
                              id="addSurveyQuestionForm"> @csrf
                            <div class="mb-2">
                                <label for="QuestionType" class="form-label">Question Type <span
                                        class="text-danger">*</span></label>
                                <select class="form-control" name="QuestionType" id="QuestionType" required>
                                    <option selected disabled>Select question type</option>
                                    @foreach(App\Enums\Feedback\SurveyQuestionTypeEnum::getAll() as $QuestionType)
                                        <option value="{{ $QuestionType->value }}">{{ $QuestionType->name }}
                                            - {{ $QuestionType->description() }}</option>
                                    @endforeach
                                </select>
                                <p id="QuestionType_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-2">
                                <label class="form-label" for="SurveyQuestion">Survey Question <span
                                        class="text-danger">*</span></label>
                                <textarea name="SurveyQuestion" id="SurveyQuestion" rows="2" class="form-control"
                                          maxlength="500"></textarea>
                                <p id="SurveyQuestion_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-2">
                                <label class="form-label" for="SurveyHelp">Notes </label>
                                <textarea name="SurveyHelp" id="SurveyHelp" rows="3" class="form-control"
                                          maxlength="1000"></textarea>
                                <p id="SurveyHelp_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="addSurveyQuestionBtn" type="submit"><i
                                        class="fas fa-save"></i>
                                    add question
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item" id="updateSurveyQuestionModal">
                        <form action="{{ route('survey-question.store',[$survey->SurveyID]) }}" method="post"
                              id="updateSurveyQuestionForm"> @csrf
                            <div class="mb-2"> @method('put')
                                <label class="form-label" for="e_SurveyQuestion">Survey Question <span
                                        class="text-danger">*</span></label>
                                <textarea name="SurveyQuestion" id="e_SurveyQuestion" rows="2" class="form-control"
                                          maxlength="500"></textarea>
                                <p id="e_SurveyQuestion_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-2">
                                <label class="form-label" for="e_SurveyHelp">Notes </label>
                                <textarea name="SurveyHelp" id="e_SurveyHelp" rows="3" class="form-control"
                                          maxlength="1000"></textarea>
                                <p id="e_SurveyHelp_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="updateSurveyQuestionBtn" type="submit"><i
                                        class="fas fa-save"></i>
                                    update question
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item text-center"
                         id="removeSurveyQuestionModal">
                        <h4 class="text-danger">
                            Remove survey question <b id="removeSurveyQuestion"></b> ?
                        </h4>
                        <form id="removeSurveyQuestionForm" method="post"> @csrf
                            <div class="mt-4">@method('delete')
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    no, cancel
                                </button>
                                <button class="btn btn-danger float-end" id="removeSurveyQuestionBtn"
                                        type="submit"><i
                                        class="fas fa-trash"></i> remove question
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item" id="addSurveyQuestionOptionModal">
                        <form method="post" id="addSurveyQuestionOptionForm"> @csrf
                            <div class="mb-2">
                                <label class="form-label" for="QuestionOption">Question Option <span
                                        class="text-danger">*</span></label>
                                <textarea name="QuestionOption" id="QuestionOption" rows="2" class="form-control"
                                          maxlength="500"></textarea>
                                <p id="QuestionOption_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-2">
                                <label class="form-label" for="QuestionOptionHelp">Option Help </label>
                                <textarea name="QuestionOptionHelp" id="QuestionOptionHelp" rows="3"
                                          class="form-control" maxlength="1000"></textarea>
                                <p id="QuestionOptionHelp_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="addSurveyQuestionOptionBtn" type="submit">
                                    <i
                                        class="fas fa-save"></i>
                                    add question
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item"
                         id="addSurveyQuestionMultipleOptionsModal">
                        <form method="post" id="addSurveyQuestionMultipleOptionsForm"> @csrf
                            <h3 id="addSurveyQuestionMultipleOptions" class="text-center"></h3>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="addSurveyQuestionMultipleOptionsBtn"
                                        type="submit">
                                    <i
                                        class="fas fa-save"></i>
                                    add options
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item text-center"
                         id="removeSurveyQuestionOptionModal">
                        <h4 class="text-danger">
                            Remove option <b id="removeSurveyQuestionOption"></b> ?
                        </h4>
                        <form id="removeSurveyQuestionOptionForm" method="post"> @csrf
                            <div class="mt-4">@method('delete')
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    no, cancel
                                </button>
                                <button class="btn btn-danger float-end" id="removeSurveyQuestionOptionBtn"
                                        type="submit"><i
                                        class="fas fa-trash"></i> remove option
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item text-center" id="submitSurveyModal">
                        <h4 class="text-success">Submit Survey <b>{{ $survey->SurveyID }}</b> : {{ $survey->Label }} for
                            Approval ? </h4>
                        <p class="text-muted">This action is non reversible, are you sure ?</p>
                        <form id="submitSurveyForm" method="post"
                              action="{{ route('surveys.submit',[$survey->SurveyID]) }}"> @csrf @method('put')
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    no, cancel
                                </button>
                                <button class="btn btn-success float-end" id="submitSurveyBtn"
                                        type="submit"><i
                                        class="fas fa-check"></i> yes, submit
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
    
    <script src='{{ asset('assets/libs/moment/moment-with-locales.js') }}'></script>
    <script>let filtersTable = null;
        const $Modal = $('#SurveyActionsModal');
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';

            $(document).on('click', '.survey-submit-approval', function () {
                $(".modal-title").html('Submit survey : {{ $survey->SurveyID }}');
                $(".modal-item").addClass('d-none');
                $('#submitSurveyModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#submitSurveyForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#submitSurveyBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.trash-question-option', function () {
                const stuff = $(this).data('info').split('~');
                $(".modal-title").html('<b class="text-danger">Remove </b> option : ' + stuff[1]);
                $(".modal-item").addClass('d-none');
                $("#removeSurveyQuestionOptionForm").attr('action', stuff[0]);
                $("#removeSurveyQuestionOption").html(stuff[1]);
                $('#removeSurveyQuestionOptionModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#removeSurveyQuestionOptionForm').submit(async function (e) {
                e.preventDefault();
                let response = await saveForm($(this), $('#removeSurveyQuestionOptionBtn'), false, true, true)
                if (response) {
                    fetchQuestions(response.question);
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.add-question-option', function () {
                const stuff = $(this).data('info').split('~');
                $(".modal-title").html('<b class="text-info">Add Option</b> to question : ' + stuff[1]);
                $(".modal-item").addClass('d-none');
                $("#addSurveyQuestionOptionForm").attr('action', stuff[0]);
                $('#addSurveyQuestionOptionModal').removeClass('d-none');
                $Modal.modal('show');
            });

            $('form#addSurveyQuestionOptionForm').submit(async function (e) {
                e.preventDefault();
                let response = await saveForm($(this), $('#addSurveyQuestionOptionBtn'), false, true, true)
                if (response) {
                    fetchQuestions(response.question);
                    $Modal.modal('hide');
                }
            });
            $(document).on('click', '.add-question-option-5', function () {
                const stuff = $(this).data('info').split('~');
                $(".modal-title").html('<b class="text-info">Add Options</b> 1 - 5 to question : ' + stuff[1]);
                $(".modal-item").addClass('d-none');
                $("#addSurveyQuestionMultipleOptions").html('<b class="text-info">Add Options</b> 1 - 5 to question')
                $("#addSurveyQuestionMultipleOptionsForm").attr('action', stuff[0]);
                $('#addSurveyQuestionMultipleOptionsModal').removeClass('d-none');
                $Modal.modal('show');
            });

            $(document).on('click', '.add-question-option-2', function () {
                const stuff = $(this).data('info').split('~');
                $(".modal-title").html('<b class="text-info">Add Options</b> Yes No to question : ' + stuff[1]);
                $(".modal-item").addClass('d-none');
                $("#addSurveyQuestionMultipleOptions").html('<b class="text-info">Add Options</b> Yes No to question')
                $("#addSurveyQuestionMultipleOptionsForm").attr('action', stuff[0]);
                $('#addSurveyQuestionMultipleOptionsModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#addSurveyQuestionMultipleOptionsForm').submit(async function (e) {
                e.preventDefault();
                let response = await saveForm($(this), $('#addSurveyQuestionMultipleOptionsBtn'), false, true, true)
                if (response) {
                    fetchQuestions(response.question);
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.data-update-question', function () {
                $(".modal-title").html('<b class="text-success">Update</b> a question to survey : {{ $survey->SurveyID }}');
                $(".modal-item").addClass('d-none');
                const stuff = $(this).data('info').split('~');
                $("#updateSurveyQuestionForm").attr('action', stuff[0]);
                $("#e_SurveyQuestion").html(stuff[1]);
                $("#e_SurveyHelp").html(stuff[2]);
                $('#updateSurveyQuestionModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#updateSurveyQuestionForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#updateSurveyQuestionBtn'), true, true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.data-remove-question', function () {
                $(".modal-title").html('<b class="text-danger">Removed</b> question from  survey : {{ $survey->SurveyID }}');
                $(".modal-item").addClass('d-none');
                const stuff = $(this).data('info').split('~');
                $("#removeSurveyQuestionForm").attr('action', stuff[0]);
                $("#removeSurveyQuestion").html(stuff[1]);
                $('#removeSurveyQuestionModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#removeSurveyQuestionForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#removeSurveyQuestionBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.survey-add-question', function () {
                $(".modal-title").html('<b class="text-success">Add</b> a question to survey : {{ $survey->SurveyID }}');
                $(".modal-item").addClass('d-none');
                $('#addSurveyQuestionModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#addSurveyQuestionForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#addSurveyQuestionBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.modal-trash-survey', function () {
                $(".modal-title").html('<b class="text-danger">Trash</b> Survey : {{ $survey->SurveyID }}');
                $(".modal-item").addClass('d-none');
                $('#trashSurveyModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#trashSurveyForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#trashSurveyBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.modal-update-survey', function () {
                $(".modal-title").html('<b class="text-info">Update</b> Survey : {{ $survey->SurveyID }}');
                $(".modal-item").addClass('d-none');
                $('#updateSurveyModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#updateSurveyForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#updateSurveyBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            const Start = flatpickr("#Start", {
                altInput: true,
                minuteIncrement: 1,
                altFormat: "F j, Y",
                dateFormat: "Y-m-d",
                allowInput: true,
                minDate: moment().add(10, 'm').format('YYYY-MM-DD'),
                defaultDate: "{{ $survey->StartOn?->format('Y-m-d') }}"
            });

            flatpickr('#End', {
                altInput: true,
                minuteIncrement: 1,
                altFormat: "F j, Y",
                dateFormat: "Y-m-d",
                allowInput: true,
                defaultDate: "{{ $survey->EndOn?->format('Y-m-d') }}",
                minDate: moment().add(10, 'm').format('YYYY-MM-DD'),
                onChange: function (selectedDates) {
                    const startDate = Start.selectedDates[0];
                    const endDate = selectedDates[0];
                    if (endDate < startDate) {
                        Start.setDate(endDate);
                    }
                }
            });

            @foreach($questions as $question)
            @if($question->Type->value === \App\Enums\Feedback\SurveyQuestionTypeEnum::Closed->value)
            fetchQuestions('{{ $question->SurveyQuestionId }}');
            @endif
            @endforeach
        });

        function fetchQuestions(questionID) {
            if (!$.fn.DataTable.isDataTable('#' + questionID + 'AnswersTable')) {
                $('#' + questionID + 'AnswersTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    dom: 't',//'rtip',
                    ajax: {//survey-question/{survey-question}/survey-question-answer
                        url: '{{ url('/') }}/survey-question/' + questionID + '/survey-question-answer',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'Answer', name: 'Answer'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "No Options provided"
                    }
                }).on('error', function (er) {
                    nWarning("an issue occurred while loading question options.");
                    console.log(er);
                });
            } else {
                $('#' + questionID + 'AnswersTable').DataTable().ajax.reload();
            }
        }

    </script>
@endsection
