@php use App\Enums\Core\ExtensionsEnum;use App\Enums\TicketSourceEnum;use App\Enums\TicketStatusEnum;use App\Helpers\SystemHelper;use App\Models\Auth\Team;use App\Models\Auth\User;use App\Models\BR\Client;use App\Models\CRM\Lead;use App\Services\CRM\TicketService;use App\Services\DMS\DocumentService; @endphp
@php @endphp
@php @endphp
@php @endphp
@extends('layouts.app')

@section('title')
    {{ $ticket->TicketID }}
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/summernote/summernote-bs5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/dropzone/dropzone.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-8 col-xxl-9">
            <div class="card">
                <div class="card-body pb-0">
                    <h3 class="h3"><b> {{ $ticket->TicketID }}</b> &nbsp;-&nbsp;{{ $ticket->Title }} </h3>
                    @if($ticket->Status->value === TicketStatusEnum::Active->value)
                        <div class="w-100">
                            <button type="button" class="btn btn-secondary m-2 modal-update-ticket"><i
                                    class="fas fa-edit"></i>&nbsp; update
                            </button>
                            <button type="button" class="btn btn-secondary m-2" id="action-file-upload"><i
                                    class="fas fa-cloud-upload"></i>&nbsp; upload files
                            </button>
                            <button type="button" class="btn btn-secondary m-2 modal-cancel-ticket"><i
                                    class="fas fa-trash-alt"></i>&nbsp;
                                cancel
                            </button>
                            <button type="button" class="btn btn-secondary m-2 modal-resolve-ticket"><i
                                    class="fas fa-check-circle"></i>&nbsp;
                                resolve
                            </button>
                        </div>
                    @elseif(in_array($ticket->Status->value, [TicketStatusEnum::Cancelled->value, TicketStatusEnum::Resolved->value],true))
                        <div class="w-100">
                            <button type="button" class="btn btn-secondary m-2 modal-reopen-ticket"><i
                                    class="fas fa-history"></i>&nbsp;
                                reopen
                            </button>
                        </div>
                    @elseif($ticket->Status->value === TicketStatusEnum::Approval->value && $canApprove)
                        <div class="w-100">
                            <button type="button" class="btn btn-primary modal-ticket-approve m-2 ">
                                <i class="fas fa-check"></i> approve
                            </button>
                            <button type="button" class="btn btn-danger modal-ticket-reject m-2">
                                <i class="fas fa-times"></i> reject
                            </button>
                        </div>
                    @endif
                    <div style="text-align: justify !important;">
                        {!! $ticket->Notes !!}
                    </div>
                </div>
                <div class="card-footer" id="ticketsAttachementContents">
                    @foreach($ticket->documents()->get(['t_Documents.Id', 't_Documents.DocumentId','MimeType','Name']) as $document)
                        {!! (new DocumentService($document))->summaryList() !!}
                    @endforeach
                </div>
            </div>

            <div class="card d-none" id="uploadCard">
                <div class="card-header pb-1 ">
                    <h3 class="card-title">Upload Document <span class="float-end" style="cursor: pointer;"
                                                                 id="uploadCardClose"><i
                                class="fas fa-times"></i></span></h3>
                </div>
                <div class="card-body p-0 border border-top">
                    <form action="{{ route('ticket.upload',[$ticket->TicketID]) }}" class="dropzone"
                          id="upload-form">@csrf</form>
                </div>
            </div>

            <div class="card">
                <div class="card-header p-0">
                    <div class="nav nav-pills card-header py-2">
                        <ul class="nav" role="tablist">
                            <li class="nav-item"><a class="nav-link active" href="#tab-comments"
                                                    data-bs-toggle="tab" role="tab" aria-selected="false"
                                >comments</a></li>
                            <li class="nav-item"><a class="nav-link" href="#tab-watchers" data-bs-toggle="tab"
                                                    role="tab" aria-selected="false" onclick="fetchWatchersTable()"
                                >users & teams</a></li>
                            <li class="nav-item"><a class="nav-link" href="#tab-activities" data-bs-toggle="tab"
                                                    role="tab" aria-selected="false" onclick="fetchActivitiesTable()"
                                >activities</a></li>
                            <li class="nav-item"><a class="nav-link" href="#tab-workflow" data-bs-toggle="tab"
                                                    role="tab" aria-selected="false" onclick="fetchWorkflowTable()"
                                >workflows</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="tab-content p-0">
                        <div class="tab-pane m-2 active show" id="tab-comments" role="tabpanel">
                            <div class="pb-1 mb-1 border-bottom">
                                Comments
                                @if($ticket->Status->value === TicketStatusEnum::Active->value)
                                    <span class="float-end">
                                          <button class="btn btn-primary btn-sm new-comment" data-parent="comments"
                                                  data-route="{{  route('ticket-comment.store',[$ticket->TicketID])  }}"
                                                  data-title="new comment" type="button">
                                        <i class="fas fa-plus-circle"></i> new comment
                                    </button>
                                </span>
                                @endif
                            </div>
                            <div id="comments" class="px-2 pt-0 w-100 comments" style="max-height: 100vh"
                                 data-url="{{  route('ticket-comment.index',[$ticket->TicketID]) }}"></div>
                            <div class="d-grid text-center" id="commentsMessage"></div>
                        </div>
                        <div class="tab-pane m-2" id="tab-workflow" role="tabpanel">
                            <table id="ticketWorkflowTable"
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
                        <div class="tab-pane m-2" id="tab-activities" role="tabpanel">
                            <table id="ticketActivitiesTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Event</th>
                                    <th>Description</th>
                                    <th>By</th>
                                    <th>Dated</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="tab-pane m-2" id="tab-watchers" role="tabpanel">
                            <table id="ticketWatchersTable"
                                   class="table table-striped no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Party</th>
                                    <th>Role</th>
                                    <th>Dated</th>
                                    <th>action</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xxl-3">
            <div class="card">
                <div class="card-body">
                    @if($party instanceof Client)
                        @include('snippets.client_summary', ['client'=>$party,'show_summary'=>true])
                    @elseif($party instanceof Lead)
                        @include('snippets.lead_summary', ['lead'=>$party, 'show_summary'=>true])
                    @elseif($party instanceof User)
                        @include('snippets.user_summary', ['user'=>$party, 'show_summary'=>true])
                    @else
                        <h3>Unknown party</h3>
                    @endif
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">Status: <b class="float-end">{{ $ticket->Status->name }}</b></li>
                        <li class="list-group-item">Priority:
                            <form class="float-end" id="ticketPriorityForm"
                                  action="{{ route('tickets.priority',[$ticket->TicketID]) }}">
                                @csrf
                                <span class="d-none" id="ticketPriorityMsg"></span>@method('put')
                                <select class="form-control text-center" name="ticket_priority" id="ticket_priority"
                                        required>
                                    @foreach(App\Enums\TicketPriorityEnum::cases() as $priority)
                                        <option
                                            value="{{ $priority->value }}" {{ ($priority->value===$ticket->Priority->value)?'selected':'' }}>{{ $priority->name }}</option>
                                    @endforeach


                                </select>
                                <p id="ticket_priority_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </form>
                        </li>
                        <li class="list-group-item">Assignee:
                            <form class="float-end" id="ticketAssigneeForm"
                                  action="{{ route('ticket.assignee',[$ticket->TicketID]) }}">
                                @csrf
                                <span class="d-none" id="ticketAssigneeMsg"></span>@method('put')
                                <select class="form-control " name="ticket_user" id="ticket_user" required>
                                    @if($ticket->assignee instanceof User)
                                        @if(SystemHelper::isSystem($ticket->assignee))
                                            <option value="{{ $ticket->assignee->UserID }}" selected
                                                    id="ticketAssignee">None - Unassigned
                                            </option>
                                        @else
                                            <option value="{{ $ticket->assignee->UserID }}" selected
                                                    id="ticketAssignee">
                                                {{ $ticket->assignee->Name }} - {{ $ticket->assignee->UserID }} (user)
                                            </option>
                                        @endif
                                    @elseif($ticket->assignee instanceof Team)
                                        <option value="t#{{ $ticket->assignee->TeamID }}" selected
                                                id="ticketAssignee">{{ $ticket->assignee->Name }} (team)
                                        </option>
                                    @endif
                                </select>
                                <p id="ticket_user_error"
                                   class="invalid-feedback d-none error col-12" role="alert"></p>
                            </form>
                        </li>
                        <li class="list-group-item">Source: <b
                                class="float-end">{!! (new TicketService($ticket))->source() !!}</b>
                        </li>
                        <li class="list-group-item">Start: <span
                                class="float-end">{{ $ticket->StartDate?->format('M d, Y') }}</span></li>
                        <li class="list-group-item">EndDate: <span
                                class="float-end">{{ $ticket->EndDate?->format('M d, Y') }}</span></li>
                    </ul>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    @include('snippets.behind_scenes',['model'=>$ticket])
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="ticketActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @switch($ticket->Status->value)
                        @case(TicketStatusEnum::Active->value)
                            <div class="onboarding-content with-gradient d-none modal-item" id="updateTicketModal">
                                <form action="{{ route('tickets.update',[$ticket->TicketID]) }}" method="post"
                                      id="updateTicketForm"> @csrf
                                    <div class="row">@method('put')
                                        @csrf
                                        <div class="mb-3 col-12">
                                            <label class="form-label" for="ticket_title">Title <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="ticket_title"
                                                   name="ticket_title"
                                                   required value="{{ $ticket->Title }}"
                                                   maxlength="200">
                                            <p id="ticket_title_error" class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>
                                        <div class="mb-3 col-sm-6 col-12">
                                            <label for="ticket_category" class="form-label">Category <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-control" name="ticket_category" id="ticket_category"
                                                    required>
                                                @foreach($TicketCategories as $ticket_category)
                                                    <option value="{{ $ticket_category->ID }}"
                                                        {{ ($ticket->CategoryID === $ticket_category->ID)?'selected':''  }}
                                                    >{{ $ticket_category->Description }}</option>
                                                @endforeach
                                            </select>
                                            <p id="ticket_category_error" class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>
                                        <div class="mb-3 col-sm-6 col-12">
                                            <label for="ticket_source" class="form-label">Source <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-control disabled" name="ticket_source"
                                                    id="ticket_source"
                                                    readonly="">
                                                @foreach(TicketSourceEnum::cases() as $ticket_source)
                                                    <option value="{{ $ticket_source->value }}"
                                                        {{ ($ticket->Source === $ticket_source->value)?'selected':''  }}
                                                    >{{ $ticket_source->name }}</option>
                                                @endforeach
                                            </select>
                                            <p id="ticket_source_error" class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>
                                        <div class="mb-3 col-12">
                                            <label class="form-label" for="ticket_description">Description </label>
                                            &nbsp;
                                            <span id="ticket_description_error"
                                                  class="invalid-feedback d-none error col-12"
                                                  role="alert"></span>
                                            <textarea name="ticket_description" id="ticket_description"
                                                      class="form-control"
                                                      rows="4"
                                                      maxlength="50000" minlength="2">{!! $ticket->Notes !!}</textarea>
                                        </div>
                                        <div class="mb-3 col-sm-6 col-12">
                                            <label class="form-label" for="ticket_start">Start</label>
                                            <input type="text" class="form-control flatpickr-datetime" id="ticket_start"
                                                   name="ticket_start" placeholder="Expect to Start."
                                                   value="{{ $ticket->StartDate?->format('Y-m-d') }}">
                                            <p id="ticket_start_error" class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>
                                        <div class="mb-3 col-sm-6 col-12">
                                            <label class="form-label" for="ticket_end">End </label>
                                            <input type="text" class="form-control flatpickr-datetime " id="ticket_end"
                                                   name="ticket_end" placeholder="Expected Completion"
                                                   value="{{ $ticket->EndDate?->format('Y-m-d') }}">
                                            <p id="ticket_end_error" class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="mt-4">
                                        <button type="button" class="btn btn-secondary float-start"
                                                data-bs-dismiss="modal">
                                            cancel
                                        </button>
                                        <button class="btn btn-primary float-end" id="updateTicketBtn" type="submit"><i
                                                class="fas fa-save"></i>
                                            update @yield('title')
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="onboarding-content with-gradient d-none modal-item" id="resolveTicketModal">
                                <h3 class="h3">Mark ticket : <b> {{ $ticket->TicketID }}</b>
                                    &nbsp;-&nbsp;{{ $ticket->Title }}
                                    as <b class=" text-success">RESOLVED</b>?
                                </h3>
                                <form action="{{ route('tickets.resolved',[$ticket->TicketID]) }}" method="post"
                                      id="resolveTicketForm"> @csrf
                                    <div class="mb-3 col-12">  @method('put')
                                        <label class="form-label" for="resolve_comment">Comment </label>
                                        <textarea name="resolve_comment" id="resolve_comment" class="form-control"
                                                  rows="2" maxlength="250" minlength="2"></textarea>
                                        <p id="resolve_comment_error" class="invalid-feedback d-none error"
                                           role="alert"></p>
                                    </div>
                                    <hr>
                                    <div class="mt-4">
                                        <button type="button" class="btn btn-secondary float-start"
                                                data-bs-dismiss="modal">
                                            cancel
                                        </button>
                                        <button class="btn btn-primary float-end" id="resolveTicketBtn" type="submit"><i
                                                class="fas fa-check-double"></i>
                                            mark @yield('title') as resolved
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="onboarding-content text-center with-gradient d-none modal-item"
                                 id="cancelTicketModal">
                                <h3 class="h3 text-danger"><b> {{ $ticket->TicketID }}</b>
                                    &nbsp;-&nbsp;{{ $ticket->Title }}
                                </h3>
                                <div class="mt-2 mb-2">
                                    Are you sure you want to cancel this ticket ?
                                </div>
                                <hr>
                                <form id="cancelTicketForm"
                                      action="{{ route('tickets.destroy',[$ticket->TicketID])  }}"
                                      method="post"> @csrf
                                    <div class="mt-4">@method('delete')
                                        <button type="button" class="btn btn-success float-start"
                                                data-bs-dismiss="modal">
                                            no, keep
                                        </button>
                                        <button class="btn btn-danger float-end" id="cancelTicketBtn" type="submit"><i
                                                class="fas fa-trash"></i> yes, cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="onboarding-content text-center with-gradient d-none modal-item"
                                 id="trashTicketWatcherModal">
                                <h3 class="h3 text-danger">Remove Watcher <b id="trashTicketWatcher"></b>
                                    from {{ $ticket->TicketID }}
                                </h3>
                                <div class="mt-2 mb-2">
                                    Are you sure you want to remove this watcher ?
                                </div>
                                <hr>
                                <form id="trashTicketWatcherForm" method="post"> @csrf
                                    <div class="mt-4">@method('delete')
                                        <button type="button" class="btn btn-success float-start"
                                                data-bs-dismiss="modal">
                                            no, keep
                                        </button>
                                        <button class="btn btn-danger float-end" id="trashTicketWatcherBtn"
                                                type="submit"><i
                                                class="fas fa-trash"></i> yes, remove
                                        </button>
                                    </div>
                                </form>
                            </div>
                            @break
                        @case(TicketStatusEnum::Cancelled->value)
                        @case(TicketStatusEnum::Resolved->value)
                            <div class="onboarding-content with-gradient d-none modal-item" id="reopenTicketModal">
                                <h3 class="h3">Re open ticket : <b> {{ $ticket->TicketID }}</b>
                                    &nbsp;-&nbsp;{{ $ticket->Title }}</h3>
                                <form action="{{ route('ticket.restore',[$ticket->TicketID]) }}" method="post"
                                      id="reopenTicketForm"> @csrf
                                    <div class="mb-3 col-12">  @method('put')
                                        <label class="form-label" for="open_reason">Re-Open Reason </label>
                                        <textarea name="open_reason" id="open_reason" class="form-control"
                                                  rows="2" maxlength="250" minlength="2"></textarea>
                                        <p id="open_reason_error" class="invalid-feedback d-none error"
                                           role="alert"></p>
                                    </div>
                                    <hr>
                                    <div class="mt-4">
                                        <button type="button" class="btn btn-secondary float-start"
                                                data-bs-dismiss="modal">
                                            cancel
                                        </button>
                                        <button class="btn btn-primary float-end" id="reopenTicketBtn" type="submit"><i
                                                class="fas fa-history"></i>
                                            reopen ticket
                                        </button>
                                    </div>
                                </form>
                            </div>
                            @break
                        @case(TicketStatusEnum::Approval->value)
                            <div class="onboarding-content with-gradient d-none modal-item text-center"
                                 id="approveTicketModal">
                                <h4 class="text-success">
                                    Approve Re-Opening Ticket <b>{{ $ticket->TicketID }}</b>
                                </h4>
                                <p class="text-muted">This action is non reversible, are you sure ?</p>
                                <form id="approveTicketForm" method="post"
                                      action="{{ route('approve-ticket.update',[$ticket->TicketID]) }}"> @csrf @method('put')
                                    <div class="mt-4">
                                        <button type="button" class="btn btn-secondary float-start"
                                                data-bs-dismiss="modal">
                                            no, cancel
                                        </button>
                                        <button class="btn btn-success float-end" id="approveTicketBtn"
                                                type="submit"><i
                                                class="fas fa-check"></i> yes, open
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="onboarding-content with-gradient d-none modal-item text-center"
                                 id="rejectTicketModal">
                                <h4 class="text-danger">
                                    Reject Re-Opening Ticket <b>{{ $ticket->TicketID }}</b>
                                </h4>
                                <p class="text-muted">This ticket will remain closed.</p>
                                <form id="rejectTicketForm" method="post"
                                      action="{{ route('approve-ticket.destroy',[$ticket->TicketID]) }}"> @csrf @method('delete')
                                    <div class="mb-3 text-start">
                                        <label class="form-label" for="ticket_reject_reason">Reject Reason
                                            <span class="text-danger">*</span></label>
                                        <textarea name="ticket_reject_reason" id="ticket_reject_reason"
                                                  class="form-control" rows="4" required
                                                  maxlength="5000"></textarea>
                                        <p id="ticket_reject_reason_error" class="invalid-feedback d-none error col-12"
                                           role="alert"></p>
                                    </div>
                                    <div class="mt-4">
                                        <button type="button" class="btn btn-secondary float-start"
                                                data-bs-dismiss="modal">
                                            no, cancel
                                        </button>
                                        <button class="btn btn-danger float-end" id="rejectTicketBtn"
                                                type="submit"><i
                                                class="fas fa-times"></i> yes, reject
                                        </button>
                                    </div>
                                </form>
                            </div>
                            @break
                    @endswitch
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    @include('snippets.actions.preview-files')
    <script src="{{ asset('assets/libs/dropzone/dropzone.min.js') }}"></script>
    <script src="{{ asset('assets/libs/summernote/summernote-bs5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>

    <script>const $Modal = $('#ticketActionsModal'), $commentsMessage = $('#commentsMessage');
        window._commentPage = '{{ route('ticket-comment.index',[$ticket->TicketID]) }}';
        Dropzone.options.uploadForm = {
            maxFilesize: 9,//Mb
            acceptedFiles: "{{ implode(", ",ExtensionsEnum::getAllMimeTypes()) }}",
            success: function (file, response) {
                file.previewElement.remove();
                $('#ticketsAttachementContents').append(response.html);
            }
        };
        $(function () {
            @switch($ticket->Status->value)
            @case(TicketStatusEnum::Active->value)

            $(document).on('click', '.modal-resolve-ticket', function () {
                $(".modal-item").addClass('d-none');
                $('#resolveTicketModal').removeClass('d-none');
                $('.modal-title').html('<b>Resolve</b> Ticket: {{ $ticket->TicketID }}');
                $Modal.children().first().removeClass('modal-lg');
                $Modal.modal('show');
            })
            $('form#resolveTicketForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#resolveTicketBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.ticket-watchers-trash', function () {
                const name = $(this).data('info');
                $(".modal-item").addClass('d-none');
                $("#trashTicketWatcherForm").attr('action', $(this).data('click_url'));
                $('#trashTicketWatcher').html(name);
                $('#trashTicketWatcherModal').removeClass('d-none');
                $('.modal-title').html('<b>Remove</b> ticket watcher : ' + name);
                $Modal.children().first().removeClass('modal-lg');
                $Modal.modal('show');
            })
            $('form#trashTicketWatcherForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#trashTicketWatcherBtn'), false, true, true)) {
                    fetchWatchersTable();
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.modal-cancel-ticket', function () {
                $(".modal-item").addClass('d-none');
                $('#cancelTicketModal').removeClass('d-none');
                $('.modal-title').html('<b>Cancel</b> Ticket: {{ $ticket->TicketID }}');
                //$('.modal-dialog').removeClass('modal-lg');
                $Modal.children().first().removeClass('modal-lg');
                $Modal.modal('show');
            })
            $('form#cancelTicketForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#cancelTicketBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $('#ticket_priority').on('change', async function () {
                let $form = $('form#ticketPriorityForm');
                let $msg = $("#ticketPriorityMsg");
                let $dropdown = $(this);
                let $selected = $dropdown.find('option:selected');

                $msg.removeClass('d-none');

                const success = await saveForm($form, $msg, false, true, true);
                $msg.addClass('d-none');

                if (success) {
                    const newVal = $selected.val();
                    const newText = $selected.text();
                    $dropdown.find('option').each(function () {
                        if ($(this).val() === newVal) {
                            $(this).text(newText);
                        }
                    });
                    $dropdown.val(newVal).trigger('change.select2');
                }
            });

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
            }).summernote('code', `{!! $ticket->Notes !!}`);

            flatpickr("#ticket_start", {
                enableTime: false,
                altInput: true,
                altFormat: "F j, Y",
                dateFormat: "Y-m-d",
            });
            flatpickr("#ticket_end", {
                enableTime: false,
                altInput: true,
                altFormat: "F j, Y",
                dateFormat: "Y-m-d",
            });

            $(document).on('click', '.modal-update-ticket', function () {
                $(".modal-title").html('Update Ticket: {{ $ticket->TicketID }}');
                $(".modal-item").addClass('d-none');
                $('#updateTicketModal').removeClass('d-none');
                $Modal.children().first().addClass('modal-lg');
                $Modal.modal('show');
            });

            $('form#updateTicketForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#updateTicketBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $('#ticket_user').select2({
                placeholder: "Select assignee", minimumInputLength: 2,
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
            }).on('change', async function () {
                let ticketAssigneeMsg = $("#ticketAssigneeMsg"), ticketAssignee = $(this);
                if ($(this).find('option:selected').attr('id') !== 'ticketAssignee') {
                    ticketAssigneeMsg.removeClass('d-none');
                    ticketAssignee.addClass('d-none');
                    await saveForm($('form#ticketAssigneeForm'), ticketAssigneeMsg, false, true, true);
                    ticketAssigneeMsg.addClass('d-none');
                    ticketAssignee.removeClass('d-none');
                    $('#ticketAssignee').remove();
                }
            });

            $("#ticket_user option[id='ticketAssignee']").prop("selected", true).trigger("change");

            @break
            @case(TicketStatusEnum::Cancelled->value)
            @case(TicketStatusEnum::Resolved->value)
            $(document).on('click', '.modal-reopen-ticket', function () {
                $(".modal-item").addClass('d-none');
                $('#reopenTicketModal').removeClass('d-none');
                $('.modal-title').html('<b>Re Open</b> Ticket: {{ $ticket->TicketID }}');
                $Modal.children().first().removeClass('modal-lg');
                $Modal.modal('show');
            })
            $('form#reopenTicketForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#reopenTicketBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });
            @break
            @case(TicketStatusEnum::Approval->value)
            $(document).on('click', '.modal-ticket-approve', function () {
                $(".modal-title").html('<b class="text-success">APPROVE</b> Ticket {{ $ticket->TicketID }} Reopen');
                $(".modal-item").addClass('d-none');
                $('#approveTicketModal').removeClass('d-none');
                $Modal.children().first().removeClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#approveTicketForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#approveTicketBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });
            $(document).on('click', '.modal-ticket-reject', function () {
                $(".modal-title").html('<b class="text-danger">REJECT</b> Ticket {{ $ticket->TicketID }} Reopen');
                $(".modal-item").addClass('d-none');
                $('#rejectTicketModal').removeClass('d-none');
                $Modal.children().first().removeClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#rejectTicketForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#rejectTicketBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });
            @break
            @endswitch

            $(document).on('click', '#action-file-upload', function () {
                $(this).addClass('d-none');
                $("#uploadCard").removeClass('d-none');

            });

            $(document).on('click', '#uploadCardClose', function () {
                $("#uploadCard").addClass('d-none');
                $("#action-file-upload").removeClass('d-none');

            });
            /* $(document).on('click', '.fetch-more-comments', function () {
                 fetchComments();
             });
             fetchComments();*/
        });

        /*async function fetchComments() {
            if (window._commentPage === null) {
                $commentsMessage.html('...');
                return;
            }
            $commentsMessage.html('<i class="fas fa-spinner fa-spin"></i> please wait');
            await $.get(window._commentPage, function (data) {
                $.map(data.data, function (item) {
                    appendComment(item);
                });
                window._commentPage = data.links.next;
                if (window._commentPage === null) {
                    $commentsMessage.html('...');
                    return;
                }
                $commentsMessage.html('<button type="button" class="btn btn-primary fetch-more-comments">Load more</button>');
            }).fail(function (e) {
                formRequest(e)
            });
        }

        function appendComment(comment, prepend = false) {
            let parent = $('#comments');

            let content = '<div id="commentBody-' + comment.id + '" class="mb-3"><div class="d-flex align-items-start">' + comment.actor.avatar +
                '<div class="flex-grow-1"><div class="float-end"><small class="text-navy">' + comment.dated.sting + '</small>';
            if (comment.permission.cancelable) {
                content += '<a class="text-danger mx-2 trash-comment" data-info="' + comment.id + '" href="javascript: void(0)"><i class="fas fa-trash"></i></a>';
            }
            content += '</div><strong>' + comment.actor.name + '</strong>' +
                '<small class="text-muted mx-2">' + comment.dated.datetime +
                '</small><p id="comment-' + comment.id + '">' + comment.msg + '</p> </div></div></div>';
            if (prepend) {
                parent.prepend(content);
            } else {
                parent.append(content);
            }
        }*/

        function fetchWorkflowTable() {
            if (!$.fn.DataTable.isDataTable('#ticketWorkflowTable')) {
                $('#ticketWorkflowTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[3, 'asc']],
                    /*"columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    */
                    ajax: {
                        url: '{{ route('ticket.workflows',[$ticket->TicketID]) }}',
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
                }).on('error', function () {
                    nWarning("an issue occurred while loading workflow.");
                    // console.log(er);
                });
            } else {
                $('#ticketWorkflowTable').DataTable().ajax.reload();
            }
        }

        function fetchWatchersTable() {
            if (!$.fn.DataTable.isDataTable('#ticketWatchersTable')) {
                $('#ticketWatchersTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[3, 'desc']],
                    ajax: {
                        url: '{{ route('ticket-watchers.index',[$ticket->TicketID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'party', name: 'party'},
                        {data: 'Role', name: 'Role'},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no workflow under this filter"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading workflow.");
                    // console.log(er);
                });
            } else {
                $('#ticketWatchersTable').DataTable().ajax.reload();
            }
        }

        function fetchActivitiesTable() {
            if (!$.fn.DataTable.isDataTable('#ticketActivitiesTable')) {
                $('#ticketActivitiesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[4, 'desc']],
                    columnDefs: [
                        // {"className": "text-center", "targets": [3]},
                        {
                            "render": function (data, type, row) {
                                return '<p><b>' + row.event + '</b><br/>' + data + '</p>';
                                //return data + " " + row.OtherNames;
                            },
                            "targets": 2 // the place of col2
                        },
                        {"visible": false, "targets": [0, 1]}
                    ],
                    ajax: {
                        url: '{{ route('ticket.activities',[$ticket->TicketID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'event', name: 'event'},
                        {data: 'description', name: 'description'},
                        {data: 'causer.Name', name: 'causer.Name'},
                        {data: 'created_at', name: 'created_at'},
                    ], "oLanguage": {
                        "sEmptyTable": "no workflow under this filter"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading workflow.");
                    // console.log(er);
                });
            } else {
                $('#ticketActivitiesTable').DataTable().ajax.reload();
            }
        }
    </script>
    @include('snippets.actions.comments', ['canComment'=>($ticket->Status->value === TicketStatusEnum::Active->value)])
@endsection
