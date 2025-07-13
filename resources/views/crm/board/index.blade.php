@php use App\Models\ThirdParies\Board; @endphp
@extends('layouts.app')

@section('title','Board')
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
@endsection
@section('content')
    <div class="row">
        <div class="col-md-3 col-xl-2">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">@yield('title')</h5>
                </div>
                <div class="list-group list-group-flush" role="tablist">
                    <a class="list-group-item list-group-item-action active" data-bs-toggle="list" href="#schedule"
                       onclick="fetchMeetingTable()" role="tab">
                        Schedule
                    </a>
                    <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#notifications"
                       role="tab">
                        Notifications
                    </a>
                    <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#committee"
                       onclick="fetchCommitteesTable();" role="tab">
                        Committees
                    </a>
                    <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#board"
                       onclick="fetchBoardMembersTable();" role="tab">
                        Board Members
                    </a>

                </div>
            </div>
        </div>
        <div class="col-md-9 col-xl-10">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="schedule" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-actions float-end">
                                @can('meeting', Board::class)
                                    <button type="button" class="btn btn-outline-primary m-1"
                                            id="triggerBoardMeetingBtn">
                                        <i class="fas fa-calendar-plus"></i>&nbsp; Schedule a Meeting
                                    </button>
                                @endcan
                            </div>
                            <h5 class="card-title mb-0">Scheduled Meetings</h5>
                        </div>
                        <div class="card-body">
                            <table id="meetingTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="notifications" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Notifications</h5>
                        </div>
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
                                                <li class="loanee-group-item">You can use <code> #name</code> to be
                                                    replaced by their name while sending.
                                                </li>
                                                <li class="loanee-group-item">You can use <code> #committee</code> to be
                                                    replaced with committee name while sending.
                                                </li>
                                                <li class="loanee-group-item">You can use <code> #date</code> to be
                                                    replaced by {{ now()->format('M d, Y') }}while sending.
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <form method="post" id="boardBulkNotificationForm"
                                  action="{{ route('bulk-notification.board') }}">
                                <div class="col-12 mb-3">@csrf
                                    <label class="form-label" for="NotificationLabel">Label <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="search-form-item form-control" required
                                           id="NotificationLabel" name="NotificationLabel"
                                           placeholder="Label">
                                    <p id="NotificationLabel_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <div class="mb-3">
                                    <label for="NotificationCommittee" class="form-label">Committee <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control" name="NotificationCommittee" id="NotificationCommittee"
                                            required>
                                        <option selected disabled>select a committee</option>
                                        @foreach($committees as $committee)
                                            <option
                                                value="{{ $committee->CommitteeID }}">{{ $committee->Name }}</option>
                                        @endforeach
                                    </select>
                                    <p id="NotificationCommittees_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <div class="mb-3 col-12">
                                    <label class="form-label" for="NotificationContent">Content <span
                                            class="text-danger">*</span></label>
                                    <b class="float-end text-info" id="msgCounter"></b>
                                    <textarea name="NotificationContent" id="NotificationContent" class="form-control"
                                              rows="4"
                                              maxlength="50000" minlength="2"></textarea>
                                    <p id="NotificationContent_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <hr>
                                <button type="submit" class="float-end btn btn-success w-50 "
                                        id="boardBulkNotificationBtn"><i class="fa fa-plane-departure"></i> send
                                    messages
                                </button>
                                <div class="clearfix"></div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="committee" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-actions float-end">
                                <button class="btn btn-primary ms-2 click-summary-data" type="button"
                                        data-click_url="{{ route('committee.create') }}"
                                        data-summary_title="Add a committee">
                                    <i class="fas fa-plus-circle"></i> Add a committee
                                </button>
                            </div>
                            <h5 class="card-title mb-0">Board committees</h5>
                        </div>
                        <div class="card-body">
                            <table id="committeesTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Name</th>
                                    <th>Notes</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="board" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-actions float-end">
                                <button class="btn btn-primary ms-2 click-summary-data" type="button"
                                        data-click_url="{{ route('board.create') }}"
                                        data-summary_title="Add a Board Member">
                                    <i class="fas fa-plus-circle"></i> Add a Board Member
                                </button>
                            </div>
                            <h5 class="card-title mb-0">Board Members</h5>
                        </div>
                        <div class="card-body">
                            <table id="boardMembersTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Committees</th>
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
    <div class="modal fade" id="boardActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @can('meeting', Board::class)
                        <div class="onboarding-content with-gradient d-none modal-item" id="createBoardMeetingModal">
                            <form action="{{ route('board-meetings.store') }}" method="post"
                                  id="createBoardMeetingForm" class="row">
                                @csrf
                                <div class="mb-3">
                                    <label for="BoardMeetingTitle" class="form-label">Title <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" required id="BoardMeetingTitle"
                                           name="BoardMeetingTitle" placeholder="Title">
                                    <p id="BoardMeetingTitle_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <div class="col-md-6 col-12 mb-3">
                                    <label class="form-label" for="BoardMeetingStart">Start <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control flatpickr-datetime" id="BoardMeetingStart"
                                           name="BoardMeetingStart" placeholder="Select start..">
                                    <p id="BoardMeetingStart_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <div class="col-md-6 col-12 mb-3">
                                    <label class="form-label" for="BoardMeetingEnd">End <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control flatpickr-datetime " id="BoardMeetingEnd"
                                           name="BoardMeetingEnd" placeholder="Select end..">
                                    <p id="BoardMeetingEnd_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <div class="col-md-6 col-12 mb-3">
                                    <label for="BoardMeetingLocation" class="form-label">Location <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control" name="BoardMeetingLocation" required
                                            id="BoardMeetingLocation">
                                        @foreach($rooms as $room)
                                            <option value="{{ $room->RoomID }}">{{ $room->Name }} - {{ $room->RoomID }}
                                                ({{ $room->Capacity }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <p id="BoardMeetingLocation_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <div class="col-md-6 col-12 mb-3">
                                    <label for="BoardMeetingCommittee" class="form-label">Committee <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control" name="BoardMeetingCommittee" id="BoardMeetingCommittee"
                                            required>
                                        <option selected disabled>select a committee</option>
                                        @foreach($committees as $committee)
                                            <option
                                                value="{{ $committee->CommitteeID }}">{{ $committee->Name }}</option>
                                        @endforeach
                                    </select>
                                    <p id="BoardMeetingCommittees_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <div class="mb-3 col-12">
                                    <label for="BoardMeetingUsers" class="form-label">Users </label>
                                    <select class="form-control " name="BoardMeetingUsers[]" id="BoardMeetingUsers"
                                            required multiple>
                                        <option value="{{ auth()->user()->UserID }}"
                                                selected>{{  auth()->user()->Name }}
                                            - {{  auth()->user()->UserID }}</option>
                                    </select>
                                    <p id="BoardMeetingUsers_error"
                                       class="invalid-feedback d-none error col-12" role="alert"></p>
                                </div>
                                <div class="mb-3 col-12">
                                    <label class="form-label" for="BoardMeetingAgenda">Agenda <span class="text-danger">*</span></label>
                                    <textarea name="BoardMeetingAgenda" id="BoardMeetingAgenda" rows="4"
                                              class="form-control" required minlength="2"></textarea>
                                    <p id="BoardMeetingAgenda_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <hr>
                                <div class="mt-4">
                                    <button type="button" class="btn btn-secondary float-start"
                                            data-bs-dismiss="modal">
                                        cancel
                                    </button>
                                    <button class="btn btn-primary float-end" id="createBoardMeetingBtn" type="submit">
                                        <i
                                            class="fas fa-save"></i> schedule board meeting
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script src='{{ asset('assets/libs/moment/moment-with-locales.js') }}'></script>
    <script src='{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}'></script>

    <script>
        let usersTable = null, rolesTable = null, teamsTable = null, branchesTable = null;
        const $Modal = $('#boardActionsModal');

        $(function () {
            $.fn.dataTable.ext.errMode = 'none';

            fetchMeetingTable();

            $('form#boardBulkNotificationForm').submit(async function (e) {
                e.preventDefault();
                await saveForm($(this), $('#boardBulkNotificationBtn'), false, true, true);
            });

            // Select2 init
            $('#BoardMeetingLocation').select2({
                allowClear: true,
                tags: true,
                placeholder: "Select Meeting Location",
                dropdownParent: $Modal,
            });

            $('#BoardMeetingUsers').select2({
                placeholder: "Select users to join",
                minimumInputLength: 2,
                dropdownParent: $Modal,
                ajax: {
                    url: '{{route('users.select2')}}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {q: $.trim(params.term)};
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {text: item.Name, id: item.UserID}
                            })
                        };
                    },
                    cache: true
                }
            });

            // Flatpickr datetime update
            const nowPlus10 = moment().add(10, 'm').format('YYYY-MM-DD HH:mm');

            const startPicker = flatpickr("#BoardMeetingStart", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                minDate: nowPlus10,
                onChange: function (selectedDates) {
                    if (selectedDates.length > 0) {
                        endPicker.set('minDate', selectedDates[0]);
                    }
                }
            });

            const endPicker = flatpickr("#BoardMeetingEnd", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                minDate: nowPlus10,
            });

            $(document).on('click', '#triggerBoardMeetingBtn', function () {
                $(".modal-item").addClass('d-none');
                $('#createBoardMeetingModal').removeClass('d-none');
                $('.modal-title').html('<b>Schedule</b> a board meeting');
                $Modal.children().first().addClass('modal-lg');
                $Modal.modal('show');
            });

            $('form#createBoardMeetingForm').submit(async function (e) {
                e.preventDefault();
                const start = startPicker.selectedDates[0];
                const end = endPicker.selectedDates[0];

                if (!start || !end || end <= start) {
                    alert("End time must be after start time.");
                    return;
                }

                let response = await saveForm($(this), $('#createBoardMeetingBtn'), false, true, true);
                if (response) {
                    $Modal.modal('hide');
                    fetchMeetingTable();
                }
            });
        });

        function fetchMeetingTable() {
            if (!$.fn.DataTable.isDataTable('#meetingTable')) {
                $('#meetingTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: '{{ route('board-meetings.index') }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'Title', name: 'Title'},
                        {data: 'StartOn', name: 'StartOn'},
                        {data: 'EndOn', name: 'EndOn'},
                        {data: 'StatusID', name: 'StatusID'},
                    ],
                    "oLanguage": {
                        "sEmptyTable": "no meetings under this filter"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading meetings.");
                });
            } else {
                $('#meetingTable').DataTable().ajax.reload();
            }
        }

        function fetchCommitteesTable() {
            if (!$.fn.DataTable.isDataTable('#committeesTable')) {
                $('#committeesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: '{{ route('committee.index') }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "CommitteeID", name: 'CommitteeID'},
                        {data: 'Name', name: 'Name'},
                        {data: 'Notes', name: 'Notes'},
                    ],
                    "oLanguage": {
                        "sEmptyTable": "no committees under this filter"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading committees.");
                });
            } else {
                $('#committeesTable').DataTable().ajax.reload();
            }
        }

        function fetchBoardMembersTable() {
            if (!$.fn.DataTable.isDataTable('#boardMembersTable')) {
                $('#boardMembersTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: '{{ route('board.index') }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "BoardMemberID", name: 'BoardMemberID'},
                        {data: 'Name', name: 'Name'},
                        {data: 'Role', name: 'Role'},
                        {data: 'committees', name: 'committees.Description'},
                    ],
                    "oLanguage": {
                        "sEmptyTable": "no board members under this filter"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading board members.");
                });
            } else {
                $('#boardMembersTable').DataTable().ajax.reload();
            }
        }
    </script>
@endsection
