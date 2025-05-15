@extends('layouts.app')

@section('title','Board Meeting')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/dropzone/dropzone.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">  {{ $meeting->Title }}
                        <button class="btn btn-primary float-end mx-2" type="button" id="triggerUpdateMeetingBtn"><i
                                class="fas fa-edit"></i></button>
                        <button class="btn btn-danger float-end  mx-2" type="button" id="triggerCancelMeetingBtn"><i
                                class="fas fa-trash-alt"></i></button>
                    </h5>
                    <p><b>Venue </b> : {{ (new \App\Services\MeetingService($meeting))->getVenue(true) }}
                        &nbsp;|&nbsp;<b>Start </b>: {{ $meeting->StartOn->format('M d, Y h:i A') }}
                        &nbsp;|&nbsp;<b>End </b>: {{ $meeting->EndOn->format('M d, Y h:i A') }}</p>
                    <p><b>Agenda </b> : {{ $meeting->Notes }}</p>
                </div>
                <div class="card-footer" id="meetingAttachmentContents">
                    <button type="button" class="btn btn-secondary m-2" id="action-file-upload"><i
                            class="fas fa-cloud-upload"></i>&nbsp; upload files
                    </button>
                    @foreach($meeting->documents()->get(['ImageID','MIMEType','Name']) as $document)
                        {!! $document?->service()->summaryList() !!}
                    @endforeach
                </div>
            </div>

            <div class="card d-none" id="uploadCard">
                <div class="card-header pb-1 ">
                    <h3 class="card-title">Upload Document
                        <span class="float-end" style="cursor: pointer;" id="uploadCardClose"><i
                                class="fas fa-times"></i></span>
                    </h3>
                </div>
                <div class="card-body p-0 border border-top">
                    <form action="{{ route('board-meetings.upload',[$meeting->MeetingID]) }}" class="dropzone"
                          id="upload-form">@csrf</form>
                </div>
            </div>
        </div>

    </div>
    <div class="modal fade" id="meetingActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item" id="updateBoardMeetingModal">
                        <form action="{{ route('board-meetings.update',[$meeting->MeetingID]) }}" method="post"
                              id="updateBoardMeetingForm" class="row">
                            @csrf
                            <div class="mb-3">@method('PUT')
                                <label for="BoardMeetingTitle" class="form-label">Title <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" required id="BoardMeetingTitle"
                                       name="BoardMeetingTitle" placeholder="Title" value="{{ $meeting->Title }}">
                                <p id="BoardMeetingTitle_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="col-md-6 col-12 mb-3">
                                <label class="form-label" for="BoardMeetingStart">Start <span
                                            class="text-danger">*</span></label>
                                <input type="text" class="form-control flatpickr-datetime" id="BoardMeetingStart"
                                       name="BoardMeetingStart" placeholder="Select start.."
                                       value="{{ $meeting->StartOn->format('Y-m-d H:i') }}">
                                <p id="BoardMeetingStart_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="col-md-6 col-12 mb-3">
                                <label class="form-label" for="BoardMeetingEnd">End <span
                                            class="text-danger">*</span></label>
                                <input type="text" class="form-control flatpickr-datetime " id="BoardMeetingEnd"
                                       name="BoardMeetingEnd" placeholder="Select end.."
                                       value="{{ $meeting->EndOn->format('Y-m-d H:i') }}">
                                <p id="BoardMeetingEnd_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="col-md-6 col-12 mb-3">
                                <label for="BoardMeetingLocation" class="form-label">Location <span
                                            class="text-danger">*</span></label>
                                <select class="form-control" name="BoardMeetingLocation" required
                                        id="BoardMeetingLocation">
                                    @if(!$meeting->room instanceof \App\Models\CRM\MeetingRoom )
                                        <option selected
                                                value="{{ (new \App\Services\MeetingService($meeting))->getVenue(true) }}">{{ (new \App\Services\MeetingService($meeting))->getVenue(true) }}</option>
                                    @endif
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->RoomID }}"
                                            {{ ($meeting->room instanceof \App\Models\CRM\MeetingRoom && $room->Id === $meeting->room->Id)?'selected':''}}
                                        >{{ $room->Name }} - {{ $room->RoomID }}
                                            ({{ $room->Capacity }})
                                        </option>
                                    @endforeach
                                </select>
                                <p id="BoardMeetingLocation_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="col-md-6 col-12 mb-3">
                                <label for="BoardMeetingUpdate" class="form-label">Send Update <span
                                            class="text-danger">*</span></label>
                                <select class="form-control" name="BoardMeetingUpdate" required
                                        id="BoardMeetingUpdate">
                                    <option selected disabled>Whether to send Notification</option>
                                    <option value="yes">Yes Send Notification</option>
                                    <option value="no">No Don't Send Notification</option>
                                </select>
                                <p id="BoardMeetingUpdate_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-3 col-12">
                                <label class="form-label" for="BoardMeetingAgenda">Agenda <span
                                        class="text-danger">*</span></label>
                                <textarea name="BoardMeetingAgenda" id="BoardMeetingAgenda" rows="4"
                                          class="form-control" required minlength="2">{{ $meeting->Notes }}</textarea>
                                <p id="BoardMeetingAgenda_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="updateBoardMeetingBtn" type="submit">
                                    <i
                                            class="fas fa-save"></i> update board meeting
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="onboarding-content text-center with-gradient d-none modal-item"
                         id="cancelBoardMeetingModal">
                        <p class="text-danger">
                            You are about to cancel this meeting <b>{{ $meeting->Title }}</b>
                        </p>
                        <div class="mt-2 mb-2">
                            Are you sure you want to cancel this meeting ?
                        </div>
                        <hr>
                        <form id="cancelBoardMeetingForm"
                              action="{{ route('board-meetings.destroy',[$meeting->MeetingID])  }}"
                              method="post"> @csrf
                            <div class="mt-4">@method('delete')
                                <button type="button" class="btn btn-success float-start"
                                        data-bs-dismiss="modal">
                                    no, keep
                                </button>
                                <button class="btn btn-danger float-end" id="cancelBoardMeetingBtn" type="submit"><i
                                        class="fas fa-trash"></i> cancel meeting
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
    <script src="{{ asset('assets/plugins/dropzone/dropzone.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/rangePlugin.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src='{{ asset('assets/plugins/moment/moment-with-locales.js') }}'></script>
    @include('snippets.actions.preview-files')
    <script>  $Modal = $('#meetingActionsModal');
        Dropzone.options.uploadForm = {
            maxFilesize: 9,//Mb
            acceptedFiles: "{{ implode(", ",\App\Enums\Core\ExtensionsEnum::getAllMimeTypes()) }}",
            success: function (file, response) {
                file.previewElement.remove();
                $('#meetingAttachmentContents').append(response.html);
            }
        };
        $(function () {
            $(document).on('click', '#triggerCancelMeetingBtn', function () {
                $(".modal-title").html('cancel Board Meeting');
                $(".modal-item").addClass('d-none');
                $('#cancelBoardMeetingModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#cancelBoardMeetingForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#cancelBoardMeetingBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '#action-file-upload', function () {
                $(this).addClass('disabled');
                $("#uploadCard").removeClass('d-none');
            });

            $(document).on('click', '#uploadCardClose', function () {
                $("#uploadCard").addClass('d-none');
                $("#action-file-upload").removeClass('disabled');
            });

            $(document).on('click', '#triggerUpdateMeetingBtn', function () {
                $(".modal-title").html('Update Board Meeting');
                $(".modal-item").addClass('d-none');
                $('#updateBoardMeetingModal').removeClass('d-none');
                $Modal.modal('show');
            });

            $('form#updateBoardMeetingForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#updateBoardMeetingBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $('#BoardMeetingLocation').select2({
                allowClear: true,
                tags: true,
                placeholder: "Select Meeting Location",
                dropdownParent: $Modal,
            });

            flatpickr("#BoardMeetingStart", {
                minDate: moment().add(10, 'm').format('YYYY-MM-DD hh:mm'),
                mode: 'range',
                dateFormat: "Y-m-d H:i",
                allowInput: true,
                enableTime: true,
                "plugins": [new rangePlugin({input: "#BoardMeetingEnd"})]
            });
        });
    </script>
@endsection
