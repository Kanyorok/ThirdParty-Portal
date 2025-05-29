@extends('layouts.app')

@section('title','Surveys')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection
@section('content')
    <div class="mb-3">
        <h1 class="h3 d-inline align-middle">@yield('title')</h1>
        <button class="btn btn-primary float-end ms-2 modal-create-survey" type="button"><i
                class="fas fa-plus-circle"></i> Add a Survey
        </button>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <table id="surveyTable"
                           class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Label</th>
                            <th>Status</th>
                            <th>Responses</th>
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
    <div class="modal fade" id="SurveyActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item" id="createSurveyModal">
                        <form action="{{ route('surveys.store') }}" method="post" id="createSurveyForm">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="Label">Label <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="Label" name="Label" required
                                       placeholder="Label">
                                <p id="Label_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Start">Start <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control flatpickr-datetime" id="Start"
                                       name="Start" placeholder="Select start.">
                                <p id="Start_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="End">End <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control flatpickr-datetime " id="End"
                                       name="End" placeholder="Select end.">
                                <p id="End_error" class="invalid-feedback d-none error col-12" role="alert"></p>
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
                                <button class="btn btn-primary float-end" id="createSurveyBtn" type="submit"><i
                                        class="fas fa-save"></i> add a survey
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
    
    <script> const $Modal = $('#SurveyActionsModal');
        let surveyTable = null;
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchSurveysTable();

            const Start = flatpickr("#Start", {
                altInput: true,
                minuteIncrement: 1,
                altFormat: "F j, Y",
                dateFormat: "Y-m-d",
                allowInput: true,
                minDate: moment().add(10, 'm').format('YYYY-MM-DD'),
            });


            flatpickr('#End', {
                altInput: true,
                minuteIncrement: 1,
                altFormat: "F j, Y",
                dateFormat: "Y-m-d",
                allowInput: true,
                minDate: moment().add(10, 'm').format('YYYY-MM-DD'),
                onChange: function (selectedDates) {
                    const startDate = Start.selectedDates[0];
                    const endDate = selectedDates[0];
                    if (endDate < startDate) {
                        Start.setDate(endDate);
                    }
                }
            });

            $(document).on('click', '.modal-create-survey', function () {
                $(".modal-title").html('Add a Survey');
                $(".modal-item").addClass('d-none');
                $('#createSurveyModal').removeClass('d-none');
                $Modal.modal('show');
            });

            $('form#createSurveyForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#createSurveyBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

        });

        function fetchSurveysTable() {
            if (surveyTable === null) {
                surveyTable = $('#surveyTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[4, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [3]}
                    ],
                    ajax: {
                        url: document.url,
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'Label', name: 'Label'},
                        {data: 'Status', name: 'Status'},
                        {data: 'responses_count', name: 'responses_count'},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no surveys under this filter"
                    }
                });

                surveyTable.on('error', function (er) {
                    nWarning("an issue occurred while loading Surveys.");
                    console.log(er);
                });
            } else {
                surveyTable.ajax.reload();
            }
        }

    </script>
@endsection
