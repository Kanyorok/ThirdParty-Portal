@php use App\Models\Auth\User; @endphp
@extends('layouts.app')

@section('title','My Schedule')
@section('styles')
    {{--<link rel="stylesheet" href="{{ asset('assets/libs/fullcalendar/fullcalendar.min.css') }}">
    <link href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css' rel='stylesheet'>--}}
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }

        .avatars {
            display: flex;
            list-style-type: none;
            margin: auto;
            padding: 0px;
            flex-direction: row;

            & __item {
                background-color: #596376;
                border: 2px solid #1f2532;
                border-radius: 100%;
                color: #ffffff;
                display: block;
                font-family: sans-serif;
                font-size: 12px;
                font-weight: 100;
                height: 45px;
                width: 45px;
                line-height: 45px;
                text-align: center;
                transition: margin 0.1s ease-in-out;
                overflow: hidden;
                margin-left: -10px;

                &:first-child {
                    z-index: 5;
                }

                &:nth-child(2) {
                    z-index: 4;
                }

                &:nth-child(3) {
                    z-index: 3;
                }

                &:nth-child(4) {
                    z-index: 2;
                }

                &:nth-child(5) {
                    z-index: 1;
                }

                &:last-child {
                    z-index: 0
                }

                img {
                    width: 100%
                }
            }

            &:hover {
                .avatars__item {
                    margin-right: 10px;
                }
            }
        }

        /* Hide event time completely */
        .fc-event-time {
            display: none !important;
        }

        /* Event title styling */
        .fc-event-title {
            white-space: normal !important;
            overflow: hidden !important;
            text-overflow: ellipsis;
            display: block !important;
            font-size: 0.85em;
            line-height: 1.2em;
            word-break: break-word;
        }

        /* Container styling */
        .fc-timegrid-event-harness .fc-timegrid-event {
            padding: 4px !important;
            min-height: 50px !important;
            display: flex !important;
            align-items: center !important; /* vertical centering */
            justify-content: flex-start !important;
            white-space: normal !important;
            word-break: break-word;
            overflow: hidden !important;
        }

        /* Main content */
        .fc-timegrid-event .fc-event-main {
            white-space: normal !important;
            overflow: hidden !important;
            word-wrap: break-word;
            font-size: 0.85em;
            width: 100%;
            line-height: 1.2em;
        }


    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-6 mb-1"></div>
        <div class="col-6 mb-1">
            <div class="float-end">
                {{--<button type="button" class="btn btn-outline-primary modal-create-call-schedule"><i class="fas fa-calendar-plus"></i>&nbsp; add scheduled call</button>
                <button type="button" class="btn btn-outline-primary modal-create-meeting-schedule"><i class="fas fa-calendar-plus"></i>&nbsp; add scheduled meeting</button>--}}
                @can('meetings',User::class)
                    <button type="button" class="btn btn-outline-primary m-1" id="triggerStaffMeetingBtn"><i
                            class="fas fa-calendar-plus"></i>&nbsp; Staff Meeting
                    </button>
                @endcan
            </div>
        </div>
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <div id='fullcalendar'></div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="scheduleActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @can('meetings',User::class)
                        <div class="onboarding-content with-gradient d-none modal-item" id="createStaffMeetingModal">
                            <form action="{{ route('user-meetings.store') }}" method="post"
                                  id="createStaffMeetingForm" class="row">
                                @csrf
                                <div class="col-md-6 col-12 mb-3">
                                    <label for="StaffMeetingTitle" class="form-label">Title <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" required id="StaffMeetingTitle"
                                           name="StaffMeetingTitle" placeholder="Title">
                                    <p id="StaffMeetingTitle_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <div class="col-md-6 col-12 mb-3">
                                    <label for="StaffMeetingLocation" class="form-label">Location <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control" name="StaffMeetingLocation" required
                                            id="StaffMeetingLocation">
                                        @foreach($rooms as $room)
                                            <option value="{{ $room->RoomID }}">{{ $room->Name }} - {{ $room->RoomID }}
                                                ({{ $room->Capacity }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <p id="StaffMeetingLocation_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <div class="col-md-6 col-12 mb-3">
                                    <label class="form-label" for="StaffMeetingStart">Start <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control flatpickr-datetime" id="StaffMeetingStart"
                                           name="StaffMeetingStart" placeholder="Select start..">
                                    <p id="StaffMeetingStart_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <div class="col-md-6 col-12 mb-3">
                                    <label class="form-label" for="StaffMeetingEnd">End <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control flatpickr-datetime " id="StaffMeetingEnd"
                                           name="StaffMeetingEnd" placeholder="Select end..">
                                    <p id="StaffMeetingEnd_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <div class="mb-3 col-12">
                                    <label for="StaffMeetingUsers" class="form-label">Users & Teams </label>
                                    <select class="form-control " name="StaffMeetingUsers[]" id="StaffMeetingUsers"
                                            required multiple>
                                        <option value="{{ auth()->user()->UserID }}"
                                                selected>{{  auth()->user()->Name }}
                                            - {{  auth()->user()->UserID }}</option>
                                    </select>
                                    <p id="StaffMeetingUsers_error"
                                       class="invalid-feedback d-none error col-12" role="alert"></p>
                                </div>
                                <div class="mb-3 col-12">
                                    <label class="form-label" for="StaffMeetingAgenda">Agenda <span class="text-danger">*</span></label>
                                    <textarea name="StaffMeetingAgenda" id="StaffMeetingAgenda" rows="4"
                                              class="form-control" required minlength="2"></textarea>
                                    <p id="StaffMeetingAgenda_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <hr>
                                <div class="mt-4">
                                    <button type="button" class="btn btn-secondary float-start"
                                            data-bs-dismiss="modal">
                                        cancel
                                    </button>
                                    <button class="btn btn-primary float-end" id="createStaffMeetingBtn" type="submit">
                                        <i
                                            class="fas fa-save"></i> schedule staff meeting
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endcan
                    {{--
                    <!--todo @deprecated -->
                    <div class="onboarding-content with-gradient d-none modal-item" id="createScheduleModal">
                        <form action="{{ route('schedule.store') }}" method="post"
                              id="createScheduleForm">
                            @csrf
                            <input type="hidden" name="_type" id="scheduleType" class="d-none">
                            <div class="mb-3">
                                <label for="client" class="form-label">Member(s)</label>
                                <select class="form-control clients" name="client[]" id="client">
                                </select>
                                <p id="client_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label for="branches" class="form-label">Branches(s)</label>
                                <select class="form-control" name="branches[]" multiple id="branches">
                                    @foreach($branches as $branch)
                                        <option
                                            value="{{ $branch->OurBranchID }}">{{ \Illuminate\Support\Str::title($branch->BranchName) }}</option>
                                    @endforeach
                                </select>
                                <p id="branches_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="schedule_title">Title </label>
                                <input type="text" class="form-control" id="schedule_title" name="schedule_title" placeholder="Title">
                                <p id="schedule_title_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="schedule_location">Location </label>
                                <input type="text" class="form-control" id="schedule_location" name="schedule_location" placeholder="Location">
                                <p id="schedule_location_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="schedule_start">Start </label>
                                <input type="text" class="form-control flatpickr-datetime" id="schedule_start" name="schedule_start" placeholder="Select start..">
                                <p id="schedule_start_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="schedule_end">End </label>
                                <input type="text" class="form-control flatpickr-datetime " id="schedule_end" name="schedule_end" placeholder="Select end..">
                                <p id="schedule_end_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="notes">Notes </label>
                                <textarea name="notes" id="notes" rows="3" class="form-control" required minlength="2"></textarea>
                                <p id="notes_end_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="createScheduleBtn" type="submit"><i
                                        class="fas fa-save"></i> add schedule
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content d-none modal-item text-center" id="deleteScheduleModal">
                        <h4 class="text-danger">
                            Cancel Schedule <b id="deleteScheduleTitle"></b> ?
                        </h4>
                        <div class="mt-2 mb-2">
                            You are about to cancel this schedule, confirm below ?
                        </div>
                        <hr>
                        <form id="deleteScheduleForm" method="post"> @csrf @method('delete')
                            <div class="mt-4">
                                <button type="button" class="btn btn-success float-start"
                                        data-bs-dismiss="modal">
                                    no, keep
                                </button>
                                <button class="btn btn-danger float-end" id="deleteScheduleBtn" type="submit"><i
                                        class="fas fa-trash"></i> yes, cancel
                                </button>
                            </div>
                        </form>
                    </div>
                    --}}
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ asset('assets/libs/rangePlugin.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.14/index.global.min.js"
            integrity="sha512-JEbmnyttAbEkbkpvW1vRqBzY3Otrp0DFwux9+JQ6kXe2mQfUmBpImuREMZS0advTaaCMotaYB5gIng/uPw3r6w=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src='{{ asset('assets/libs/moment/moment-with-locales.js') }}'></script>
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script>
        let $Modal = $('#scheduleActionsModal');
        const _type = $('#scheduleType'), windowLocation = window.location.toString();
        let start = null, end = null, client = null;
        $(function () {
            window.calendar = new FullCalendar.Calendar(document.getElementById('fullcalendar'), {
                initialView: 'timeGridWeek',
                themeSystem: 'bootstrap5',
                displayEventTime: true,
                navLinks: true,
                height: 700,
                nowIndicator: true,
                businessHours: [
                    {
                        daysOfWeek: [1, 2, 3, 4, 5],
                        startTime: '08:00',
                        endTime: '18:00',
                    },
                    {
                        daysOfWeek: [6],
                        startTime: '08:00',
                        endTime: '14:00'
                    }
                ],
                headerToolbar: {
                    left: 'prev,next today',
                    center: "title",
                    right: "dayGridMonth,timeGridWeek,timeGridDay,listMonth"
                },
                weekNumbers: true,
                dayMaxEvents: true,
                editable: true,
                eventRender: function (event, element, view) {
                    event.allDay = event.allDay === 'false';
                },
                selectable: true,
                selectHelper: true,
                events: function (info, successCallback, failureCallback) {
                    let start = moment(info.start.valueOf()).format('YYYY-MM-DD'),
                        end = moment(info.end.valueOf()).format('YYYY-MM-DD');
                    $.ajax({
                        url: windowLocation.replace('#', '') + "?start=" + start + "&end=" + end,
                        type: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': window.csrf_token,
                            'accept': 'application/json'
                        }, success: function (response) {
                            successCallback(response.data);
                        }, error: function (xhr, status, error) {
                            failureCallback(error);
                        }
                    });
                },
                select: function (info) {
                    showCreateModal(moment(info.start.valueOf()).format('YYYY-MM-DD HH:mm'), moment(info.end.valueOf()).format('YYYY-MM-DD HH:mm'), 'call')
                },
                eventChange: function (eventInfo) {
                    if (!eventInfo.event.extendedProps.permission.editable) {
                        nWarning('Schedule cannot be moved');
                        eventInfo.revert();
                        return;
                    }
                    let ended = moment(eventInfo.event.end.valueOf());
                    let start = moment(eventInfo.event.start.valueOf());
                    if (ended.isBefore(moment().startOf('day'))) {
                        nWarning('Schedule cannot be moved');
                        eventInfo.revert();
                        return;
                    }
                    if (ended.diff(start, 'hours') > 8) {
                        nWarning('Schedule cannot be more than 6 hours');
                        eventInfo.revert();
                        return;
                    }
                    $.ajax({
                        url: windowLocation.replace('#', '') + "/" + eventInfo.event._def.publicId,
                        type: 'PUT',
                        data: [{name: '_token', value: window.csrf_token}, {
                            name: 'schedule_start',
                            value: start.format('YYYY-MM-DD HH:mm')
                        }, {name: 'schedule_end', value: ended.format('YYYY-MM-DD HH:mm')}, {
                            name: 'notes',
                            value: eventInfo.event.extendedProps.description
                        }, {name: 'client[]', value: 'RI'}],
                        headers: {
                            'X-CSRF-TOKEN': window.csrf_token,
                            'accept': 'application/json'
                        }, success: function (response) {
                            nSuccess(response.message);
                        }, error: function (xhr, status, error) {
                            nWarning('Schedule moving failed');
                            eventInfo.revert();
                        }
                    });
                },
                eventClick: function (eventInfo) {
                    let event = eventInfo.event;
                    showOffCanvasMain(event.extendedProps.icon + ' ' + event.title, windowLocation.replace('#', '') + '/' + event._def.publicId);
                }
            });
            window.calendar.render();

            $(document).on('click', '#triggerStaffMeetingBtn', function () {
                $(".modal-item").addClass('d-none');
                $('#createStaffMeetingModal').removeClass('d-none');
                $('.modal-title').html('<b>Schedule</b> a staff meeting');
                $Modal.children().first().addClass('modal-lg');
                $Modal.modal('show');
            });

            $('form#createStaffMeetingForm').submit(async function (e) {
                e.preventDefault();
                let response = await saveForm($(this), $('#createStaffMeetingBtn'), false, true, true);
                if (response) {
                    $Modal.modal('hide');
                    if (window.calendar !== null) {
                        window.calendar.addEvent(response.event);
                    }
                }
            });

            $('#StaffMeetingUsers').select2({
                placeholder: "Select user(s) &/or team to join",
                minimumInputLength: 2,
                dropdownParent: $Modal,
                ajax: {
                    url: '{!! route('users.select2', ['with_teams'=>'rzr.co.ke', 'add_all'=>'rzr.co.ke']) !!}',
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

            $('#StaffMeetingLocation').select2({
                allowClear: true,
                tags: true,
                placeholder: "Select Meeting Location or Type a Location",
                dropdownParent: $Modal,
            });

            const now = moment().add(10, 'm').format('YYYY-MM-DD HH:mm');

            flatpickr("#StaffMeetingStart", {
                enableTime: true,
                altInput: true,
                minuteIncrement: 1,
                minDate: now,
                defaultDate: now,
                altFormat: "Y-m-d H:i",
                dateFormat: "Y-m-d H:i",
                onChange: function (selectedDates) {
                    if (selectedDates.length) {
                        let startTime = moment(selectedDates[0]);
                        let endTime = startTime.clone().add(30, 'minutes');
                        $("#StaffMeetingEnd").val(endTime.format('YYYY-MM-DD HH:mm'));
                        if (end) {
                            end.setDate(endTime.toDate());
                        }
                    }
                }
            });

            end = flatpickr("#StaffMeetingEnd", {
                enableTime: true,
                altInput: true,
                minuteIncrement: 1,
                minDate: now,
                altFormat: "Y-m-d H:i",
                dateFormat: "Y-m-d H:i"
            });

            /* $(document).on('click', '.modal-create-call-schedule', function () {
                 showCreateModal(moment().format('YYYY-MM-DD HH:mm'), moment().add(30,'m').format('YYYY-MM-DD HH:mm'),'call');
             });

             $(document).on('click', '.modal-create-meeting-schedule', function () {
                 showCreateModal(moment().format('YYYY-MM-DD HH:mm'), moment().add(30,'m').format('YYYY-MM-DD HH:mm'), 'meeting');
             });

             $('#client').on('change', function() {
                 if (_type.val()==='call'){
                     let name = $(this).find(":selected").text().split('-')[0];
                     if (name.length >0){
                         $('#schedule_title').val("Call with "+name);
                     }
                 }
             });

             $('form#createScheduleForm').submit(async function (e) {
                 e.preventDefault();
                 if (validateDates()){
                     let response = await saveForm($(this), $('#createScheduleBtn'),false, true, true);
                     if (response){
                         $Modal.modal('hide');
                         if (window.calendar !== null) {
                             window.calendar.addEvent(response.event);
                         }
                     }
                 }
             }); */

            $('form#deleteScheduleForm').submit(async function (e) {
                e.preventDefault();
                let data = await saveForm($(this), $('#deleteScheduleBtn'), false, true, true);
                if (data) {
                    $Modal.modal('hide');
                    window.bsOffcanvas.hide();
                    if (window.calendar !== null) {
                        let event = window.calendar.getEventById(data.event.id);
                        if (event !== null) {
                            event.remove();
                            window.calendar.addEvent(data.event);
                        }
                    }
                }
            });
        });

        /*function showCreateModal(start_time, end_time, type) {
       if (start !== null){
           start.destroy();
       }
       if (end !== null){
           end.destroy();
       }
       if (client !== null){
           client.select2('destroy');
       }

       _type.val(type);
       if(type === 'meeting'){
           $(".modal-title").html('Schedule a meeting.');
           $('#schedule_location').val('').parent('div').removeClass('d-none');
           $('#schedule_title').val('').parent('div').removeClass('d-none');
           $('#branches').val('').parent('div').removeClass('d-none');
           //$('#schedule_title').removeAttr("readonly").val('');
           client = $('#client').val([]).attr('multiple',"multiple").change().select2({
               placeholder: "Choose a client...", minimumInputLength: 2,
               dropdownParent: $Modal,
               ajax: {
                   url: '{ {route('clients.select2')}}',
                   dataType: 'json',
                   delay: 250,
                   data: function (params) {
                       return {q: $.trim(params.term)};
                   },
                   processResults: function (data) {
                       return {
                           results: $.map(data, function (item) {
                               return {text: item.Name + ' - ' + item.ClientID, id: item.ClientID}
                           })
                       };
                   },
                   cache: true
               }
           });
       } else if (type === 'call') {
           $(".modal-title").html('Schedule a call.');
           $('#schedule_location').val('').parent('div').addClass('d-none');
           $('#schedule_title').val('').parent('div').addClass('d-none');
           $('#branches').val('').parent('div').addClass('d-none');
           //$('#schedule_title').attr("readonly","readonly").val('');
           client = $('#client').val([]).removeAttr("multiple").change().select2({
               placeholder: "Choose a client...", minimumInputLength: 2,
               dropdownParent: $Modal,
               ajax: {
                   url: '{ {route('clients.select2')}}',
                   dataType: 'json',
                   delay: 250,
                   data: function (params) {
                       return {q: $.trim(params.term)};
                   },
                   processResults: function (data) {
                       return {
                           results: $.map(data, function (item) {
                               return {text: item.Name + ' - ' + item.ClientID, id: item.ClientID}
                           })
                       };
                   },
                   cache: true
               }
           });
       } else {
           nWarning('Unknown type of schedule.');
           return;
       }

       $("#schedule_start").val(start_time);
       start = flatpickr("#schedule_start", {
           enableTime: true,
           altInput: true,
           minDate: moment().format('YYYY-MM-DD hh:mm'),
           defaultDate: start_time,
           minuteIncrement: 1,
           altFormat: "F j, Y H:i",
           dateFormat: "Y-m-d H:i",
       });

       $("#schedule_end").val(end_time);
       end = flatpickr("#schedule_end", {
           enableTime: true,
           altInput: true,
           minuteIncrement: 1,
           minDate: moment().add(2,'m').format('YYYY-MM-DD hh:mm'),
           defaultDate: end_time,
           altFormat: "F j, Y H:i",
           dateFormat: "Y-m-d H:i",
       });

       $(".modal-item").addClass('d-none');
       $('#createScheduleModal').removeClass('d-none');
       $Modal.modal('show');
   }*/

    </script>
@endsection
