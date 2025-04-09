<link rel="stylesheet" href="{{ asset('assets/plugins/summernote/summernote-bs5.min.css') }}">
<script src="{{ asset('assets/plugins/summernote/summernote-bs5.min.js') }}"></script>
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
<script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
<script src='{{ asset('assets/plugins/moment/moment-with-locales.js') }}'></script>
<style>
    .select2-container {
        width: 100% !important;
    }
</style>
<div class="modal fade" id="partyTicketsActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document" id="partyTicketsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">..</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="onboarding-content with-gradient d-none modal-item" id="createPartyTicketModal">
                    <form method="post" id="createPartyTicketForm" class="row">
                        @csrf
                        @if(isset($hidden)){!! $hidden !!} @endif
                        <div class="mb-3 col-12">
                            <label class="form-label" for="ticket_title">Title <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ticket_title" name="ticket_title" required
                                   maxlength="200">
                            <p id="ticket_title_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <div class="mb-3 col-sm-4 col-12">
                            <label for="ticket_category" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-control" name="ticket_category" id="ticket_category" required>
                                <option selected disabled>select an category</option>
                                @foreach($TicketCategories as $ticket_category)
                                    <option
                                        value="{{ $ticket_category->ID }}">{{ $ticket_category->Description }}</option>
                                @endforeach
                            </select>
                            <p id="ticket_category_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <div class="mb-3 col-sm-4 col-12">
                            <label for="ticket_source" class="form-label">Source <span
                                    class="text-danger">*</span></label>
                            <select class="form-control" name="ticket_source" id="ticket_source" required>
                                @if(isset($source) && $source instanceof \App\Enums\TicketSourceEnum)
                                    <option selected value="{{ $source->value }}">{{ $source->name }}</option>
                                @else
                                <option selected disabled>select a source</option>
                                @foreach(\App\Enums\TicketSourceEnum::cases() as $ticket_source)
                                    <option value="{{ $ticket_source->value }}">{{ $ticket_source->name }}</option>
                                @endforeach
                                @endif
                            </select>
                            <p id="ticket_source_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <div class="mb-3 col-sm-4 col-12">
                            <label for="ticket_priority" class="form-label">Priority <span class="text-danger">*</span></label>
                            <select class="form-control" name="ticket_priority" id="ticket_priority" required>
                                <option selected disabled>select a priority</option>
                                @foreach(\App\Enums\TicketPriorityEnum::cases() as $ticket_priority)
                                    <option value="{{ $ticket_priority->value }}">{{ $ticket_priority->name }}</option>
                                @endforeach
                            </select>
                            <p id="ticket_priority_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <div class="mb-3 col-12">
                            <label class="form-label" for="ticket_description">Description </label> &nbsp;
                            <span id="ticket_description_error" class="invalid-feedback d-none error col-12"
                                  role="alert"></span>
                            <textarea name="ticket_description" id="ticket_description" class="form-control" rows="4"
                                      maxlength="5000" minlength="2">@if(isset($content))
                                    <br><hr>{!! $content !!} @endif</textarea>
                        </div>
                        <div class="mb-3 col-sm-6 col-12">
                            <label for="ticket_user" class="form-label">Assignee <span
                                    class="text-danger">*</span></label>
                            <select class="form-control " name="ticket_user"
                                    id="ticket_user" required>
                                <option value="{{ auth()->user()->UserID }}"
                                        selected>{{  auth()->user()->Name }} - {{  auth()->user()->UserID }}</option>
                            </select>
                            <p id="ticket_user_error"
                               class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <div class="mb-3 col-sm-6 col-12">
                            <label for="ticket_watchers" class="form-label">Watchers </label>
                            <select class="form-control" name="ticket_watchers[]" id="ticket_watchers" multiple>
                            </select>
                            <p id="ticket_watchers_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <div class="mb-3 col-sm-6 col-12">
                            <label class="form-label" for="ticket_start">Start</label>
                            <input type="text" class="form-control flatpickr-datetime" id="ticket_start"
                                   name="ticket_start" placeholder="Expect to Start.">
                            <p id="ticket_start_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <div class="mb-3 col-sm-6 col-12">
                            <label class="form-label" for="ticket_end">End </label>
                            <input type="text" class="form-control flatpickr-datetime " id="ticket_end"
                                   name="ticket_end" placeholder="Expected Completion">
                            <p id="ticket_end_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <hr>
                        <div class="mt-4 col-12">
                            <button type="button" class="btn btn-secondary float-start"
                                    data-bs-dismiss="modal">
                                cancel
                            </button>
                            <button class="btn btn-primary float-end" id="createPartyTicketBtn" type="submit"><i
                                    class="fas fa-plus-circle"></i> create ticket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        $('textarea#ticket_description').summernote({
            placeholder: 'Description of the issue',
            dialogsInBody: true,
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

        flatpickr("#ticket_start", {
            enableTime: false,
            altInput: true,
            minDate: moment().add(10, 'm').format('YYYY-MM-DD'),
            altFormat: "F j, Y",
            dateFormat: "Y-m-d",
            allowInput: true,
        });
        flatpickr("#ticket_end", {
            enableTime: false,
            altInput: true,
            minDate: moment().add(11, 'm').format('YYYY-MM-DD'),
            altFormat: "F j, Y",
            dateFormat: "Y-m-d",
            allowInput: true,
        });

        $(document).on('click', '.add-party-ticket-btn', function () {
            $(".modal-item").addClass('d-none');
            $('#createPartyTicketModal').removeClass('d-none');
            $('#createPartyTicketForm').attr('action', $(this).data('action'));
            $('.modal-title').html('Add a ticket.');
            $("#partyTicketsModal").addClass('modal-lg');
            $("#partyTicketsActionsModal").modal('show');
        });

        $('#ticket_user').select2({
            placeholder: "Select assignee", minimumInputLength: 2,
            dropdownParent: $("#partyTicketsActionsModal"),
            ajax: {
                url: '{!! route('users.select2',['add_none'=>'rzr.co.ke','with_teams'=>'rzr.co.ke']) !!}',
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

        $('#ticket_watchers').select2({
            placeholder: "Select watchers user/team", minimumInputLength: 2,
            dropdownParent: $("#partyTicketsActionsModal"),
            ajax: {
                url: '{!! route('users.select2',['with_teams'=>'rzr.co.ke']) !!}',
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

        $("#ticket_user option[value='{{  auth()->user()->UserID }}']").prop("selected", true).trigger("change")

        $('form#createPartyTicketForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#createPartyTicketBtn'), false, true, true)) {
                $('#ticket_description').summernote('code', '');
                if (typeof fetchTicketsTable === "function") {
                    fetchTicketsTable();
                }
                $("#partyTicketsActionsModal").modal('hide');
            }
        });
    });
</script>
