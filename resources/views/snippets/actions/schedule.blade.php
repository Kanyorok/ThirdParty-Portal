@php use App\Services\MeetingService; @endphp
<link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
<script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
<style>
    .select2-container {
        width: 100% !important;
    }
</style>
<div class="modal fade" id="scheduleActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">..</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="onboarding-content with-gradient d-none modal-item" id="createAppointmentModal">
                    <form method="post" id="createAppointmentForm" class="row">
                        @csrf
                        <div class="mb-3 col-sm-6 col-12">
                            <label class="form-label" for="meeting_title">Title <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="meeting_title" name="meeting_title"
                                   placeholder="Title">
                            <p id="meeting_title_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <div class="mb-3 col-sm-6 col-12">
                            <label for="meeting_location" class="form-label">Location <span
                                    class="text-danger">*</span></label>
                            <select class="form-control" name="meeting_location" required
                                    id="meeting_location">
                                <option selected disabled>Select or Type Location/Link</option>
                                @foreach(MeetingService::rooms() as $room)
                                    <option value="{{ $room->RoomID }}">{{ $room->Name }} - {{ $room->RoomID }}
                                        ({{ $room->Capacity }})
                                    </option>
                                @endforeach
                            </select>
                            <p id="meeting_location_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <div class="mb-3 col-sm-6 col-12">
                            <label class="form-label" for="meeting_start">Start <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control flatpickr-datetime" id="meeting_start"
                                   name="meeting_start" placeholder="Select start.">
                            <p id="meeting_start_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <div class="mb-3 col-sm-6 col-12">
                            <label class="form-label" for="meeting_end">End <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control flatpickr-datetime " id="meeting_end"
                                   name="meeting_end" placeholder="Select end.">
                            <p id="meeting_end_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <div class="mb-3 col-12">
                            <label for="meeting_users" class="form-label">Users <span
                                    class="text-danger">*</span></label>
                            <select class="form-control " name="meeting_users[]" id="meeting_users" required multiple>
                                <option value="{{ auth()->user()->UserID }}"
                                        selected>{{  auth()->user()->Name }} - {{  auth()->user()->UserID }}</option>
                            </select>
                            <p id="meeting_users_error"
                               class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <div class="mb-3 col-12">
                            <label class="form-label" for="meeting_notes">Notes <span
                                    class="text-danger">*</span></label>
                            <textarea name="meeting_notes" id="meeting_notes" rows="3" class="form-control" required
                                      minlength="2"></textarea>
                            <p id="meeting_notes_end_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <hr>
                        <div class="col-12">
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="createAppointmentBtn" type="submit"><i
                                        class="fas fa-calendar-plus"></i> add appointment
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="onboarding-content with-gradient d-none modal-item" id="createCallModal">
                    <form method="post" id="createCallForm" class="row">
                        @csrf
                        <div class="mb-3 col-sm-6 col-12">
                            <label class="form-label" for="call_start">Start <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control flatpickr-datetime" id="call_start"
                                   name="call_start" placeholder="Select start.">
                            <p id="call_start_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <div class="mb-3 col-sm-6 col-12">
                            <label class="form-label" for="call_end">End <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control flatpickr-datetime " id="call_end"
                                   name="call_end" placeholder="Select end.">
                            <p id="call_end_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <div class="mb-3 col-12">
                            <label for="call_user" class="form-label">User <span
                                    class="text-danger">*</span></label>
                            <select class="form-control " name="call_user"
                                    id="call_user" required>
                                <option value="{{ auth()->user()->UserID }}"
                                        selected>{{  auth()->user()->Name }} - {{  auth()->user()->UserID }}</option>
                            </select>
                            <p id="call_user_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <div class="mb-3 col-12">
                            <label class="form-label" for="call_notes">Notes <span
                                    class="text-danger">*</span></label>
                            <textarea name="call_notes" id="call_notes" rows="3" class="form-control" required
                                      minlength="2"></textarea>
                            <p id="call_notes_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <hr>
                        <div class="col-12">
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="createCallBtn" type="submit"><i
                                        class="fas fa-phone"></i>&nbsp;<i class="fas fa-calendar-plus"></i> schedule a
                                    call
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('assets/libs/rangePlugin.js') }}"></script>
<script src='{{ asset('assets/libs/moment/moment-with-locales.js') }}'></script>
<script>
    let schedule_call_start = null, schedule_call_end = null, schedule_meeting_start = null,
        schedule_meeting_end = null;
    $(function () {
        /* schedule_call_start = flatpickr("#call_start", {
             enableTime: true,
             altInput: true,
             minDate: moment().add(10, 'm').format('YYYY-MM-DD hh:mm'),
             minuteIncrement: 1,
             altFormat: "F j, Y H:i",
             dateFormat: "Y-m-d H:i",
             allowInput: true,
         });
         schedule_meeting_start = flatpickr("#meeting_start", {
             enableTime: true,
             altInput: true,
             minDate: moment().add(10, 'm').format('YYYY-MM-DD hh:mm'),
             minuteIncrement: 1,
             altFormat: "F j, Y H:i",
             dateFormat: "Y-m-d H:i",
             allowInput: true,
         });
         schedule_call_end = flatpickr("#call_end", {
             enableTime: true,
             altInput: true,
             minuteIncrement: 1,
             minDate: moment().add(40, 'm').format('YYYY-MM-DD hh:mm'),
             altFormat: "F j, Y H:i",
             dateFormat: "Y-m-d H:i",
             allowInput: true,
         });
         schedule_meeting_end = flatpickr("#meeting_end", {
             enableTime: true,
             altInput: true,
             minuteIncrement: 1,
             minDate: moment().add(40, 'm').format('YYYY-MM-DD hh:mm'),
             altFormat: "F j, Y H:i",
             dateFormat: "Y-m-d H:i",
             allowInput: true,
         });*/
        flatpickr("#call_start", {
            minDate: moment().add(10, 'm').format('YYYY-MM-DD hh:mm'),
            mode: 'range',
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            allowInput: true,
            "plugins": [new rangePlugin({input: "#call_end"})]
        });
        flatpickr("#meeting_start", {
            minDate: moment().add(10, 'm').format('YYYY-MM-DD hh:mm'),
            mode: 'range',
            dateFormat: "Y-m-d H:i",
            allowInput: true,
            enableTime: true,
            "plugins": [new rangePlugin({input: "#meeting_end"})]
        });

        $('#meeting_users').select2({
            placeholder: "Select user to assign",
            minimumInputLength: 2,
            dropdownParent: $('#scheduleActionsModal'),
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
        $('#call_user').select2({
            placeholder: "Select user to assign", minimumInputLength: 2,
            dropdownParent: $('#scheduleActionsModal'),
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

        $('#meeting_location').select2({
            allowClear: true,
            tags: true,
            placeholder: "Select Location or Type it In",
            dropdownParent: $("#scheduleActionsModal"),
        });
        /*
                $("#call_start").on("change", function () {
                    validateDates('call_start', 'call_end', true, schedule_call_end);
                });
                $("#call_end").on("change", function () {
                    validateDates('call_start', 'call_end', false, schedule_call_end);
                });
                $("#meeting_start").on("change", function () {
                    validateDates('meeting_start', 'meeting_end', true, schedule_meeting_end);
                });
                $("#meeting_end").on("change", function () {
                    validateDates('meeting_start', 'meeting_end', false, schedule_meeting_end);
                });*/


        $(document).on('click', '.add-party-scheduled-call-btn', function () {
            $(".modal-item").addClass('d-none');
            $('#createCallForm').attr('action', $(this).data('action'));
            $('.modal-title').html('Schedule a call.');
            $('#createCallModal').removeClass('d-none');
            /* schedule_call_start.setDate(new Date(moment().add(5, 'm').format('YYYY-MM-DD HH:mm')));
             schedule_call_end.setDate(new Date(moment().add(20, 'm').format('YYYY-MM-DD HH:mm')));*/
            $("#scheduleActionsModal").modal('show');
        });
        $('form#createCallForm').submit(async function (e) {
            e.preventDefault();
            if (validateDates('call_start', 'call_end')) {
                let response = await saveForm($(this), $('#createCallBtn'), false, true, true);
                if (response) {
                    if (typeof response.activity === "object" && typeof appendActivity === "function") {
                        appendActivity(response.activity);
                    }
                    if (typeof fetchScheduleTable === "function") {
                        fetchScheduleTable();
                    }
                    if (typeof response.activity === "object" && typeof appendAct === "function") {
                        appendAct($('#activitiesMain'), response.activity.html, true)
                    }
                    $("#scheduleActionsModal").modal('hide');
                }
            }
        });

        $(document).on('click', '.add-party-appointment-btn', function () {
            $(".modal-item").addClass('d-none');
            $('#createAppointmentForm').attr('action', $(this).data('action'));
            $('#createAppointmentModal').removeClass('d-none');
            $('.modal-title').html('Add an appointment.');
            /*schedule_meeting_start.setDate(new Date(moment().add(5, 'm').format('YYYY-MM-DD HH:mm')));
            schedule_meeting_end.setDate(new Date(moment().add(20, 'm').format('YYYY-MM-DD HH:mm')));*/
            $("#scheduleActionsModal").modal('show');
        });
        $('form#createAppointmentForm').submit(async function (e) {
            e.preventDefault();
            if (validateDates('meeting_start', 'meeting_end')) {
                let response = await saveForm($(this), $('#createAppointmentBtn'), false, true, true);
                if (response) {
                    if (typeof response.activity === "object" && typeof appendActivity === "function") {
                        appendActivity(response.activity);
                    }
                    if (typeof response.activity === "object" && typeof appendAct === "function") {
                        appendAct($('#activitiesMain'), response.activity.html, true)
                    }
                    if (typeof fetchScheduleTable === "function") {
                        fetchScheduleTable();
                    }
                    $("#scheduleActionsModal").modal('hide');
                }
            }
        });

    });

    function validateDates(startID, endID, isStart, end) {
        clearInvalid(startID);
        clearInvalid(endID);

        let start_time = moment($("#" + startID).val(), "YYYY-MM-DD HH:mm");

        if (!start_time.isValid()) {
            setInvalid(startID, 'Invalid date here');
            return false;
        }
        let end_time = moment($("#" + endID).val(), "YYYY-MM-DD HH:mm");
        if (isStart) {
            end_time = start_time.clone().add(15, 'm');
            end.setDate(new Date(end_time.format('YYYY-MM-DD HH:mm')));
        }

        if (!end_time.isValid()) {
            setInvalid(endID, 'Invalid date here');
            return false;
        }
        if (moment().subtract(2, 'm').isAfter(start_time)) {
            setInvalid(startID, 'cannot schedule date after now');
            return false;
        }

        if (start_time.isSame(end_time) || end_time.isBefore(start_time)) {
            setInvalid(endID, 'End time should be after start.');
            return false;
        }

        if (Math.abs(start_time.diff(end_time, 'minutes')) < 1) {
            setInvalid(endID, 'Duration should be at least a minute.');
            return false;
        }
        return true;
    }
</script>
