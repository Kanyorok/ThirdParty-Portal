@extends('layouts.app')

@section('title')
    {{ Str::limit($conversation->email->Subject,50) }}
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/summernote/summernote-bs5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/dropzone/dropzone.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }

        .accordion-item {
            border: none;
            background: inherit;
        }

        .accordion-button:not(.collapsed) {
            background-color: inherit;
            box-shadow: inset 0 -1px 0 rgba(0, 0, 0, .125);
            color: inherit;
        }

        .accordion-button {
            display: block;
            background-color: #ffffff;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-7 col-lg-8">
            <div class="card">
                <div class="card-header pb-0 border-bottom">
                    <div class="row">
                        <div class="col-2">
                            <a href="{{ route('email-conversations.index') }}" class="btn btn-primary"><i
                                    class="fas fa-backward"></i> back</a>
                        </div>
                        <div class="col-10">
                            <h3 class="card-title">{{ $conversation->email->Subject }}</h3>
                        </div>
                    </div>
                </div>
                <div class="card-body border-bottom border-1 m-1 d-none" id="DraftEmailContent">

                </div>
                <div class="card-body p-1">
                    <div class="accordion accordion-flush" id="MailConversation">
                        @foreach($conversation->emails()->latest('t_CRMEmails.Dated')->get() as $email)
                            <div class="accordion-item m-1">
                                <div class="accordion-header" id="{{ $email->EmailID }}">
                                    <div class="accordion-button {{-- (!$loop->first)?'collapsed':'' --}}" type="button"
                                         data-bs-toggle="collapse" data-bs-target="#Content{{ $email->EmailID }}"
                                         aria-expanded="{{-- ($loop->first)?'true':'false' --}}false"
                                         aria-controls="Content{{ $email->EmailID }}">
                                        @if($email->Type->value === \App\Enums\EmailTypeEnum::Incoming->value)
                                            <div
                                                class="row {{ ($email->Status->value===\App\Enums\EmailStatusEnum::Unread->value)?'fw-bold':'' }}">
                                                <div
                                                    class="col-1 p-0">{!! (new \App\Services\PartyService($email->party))->getImage('class="img-thumbnail me-2 p-0" width="40" height="40" style="max-width: none;"') !!}</div>
                                                <div class="col-11">
                                                    <p class="m-0"> {!! (new \App\Services\PartyService($email->party))->simplified(true,true, $email->From) !!}
                                                        <span
                                                            class='float-end'>{{ $email->Dated->format('M d, Y H:i') }}</span>
                                                    </p>
                                                    <p class="m-0 {{ ($email->Priority?->value === \App\Enums\EmailPriorityEnum::Important->value)?'text-warning':'' }}">@if($email->attachments()->count()>0)
                                                            <span class="text-info " title="has attachments"><i
                                                                    class="fas fa-paperclip"></i></span>
                                                        @endif {{ $email->Subject }} </p>
                                                </div>
                                            </div>
                                        @elseif($email->Type->value === \App\Enums\EmailTypeEnum::Outgoing->value)
                                            <div class="row">
                                                <div
                                                    class="col-1 p-0">{!! (new \App\Services\PartyService($email->creator))->getImage('class="img-thumbnail me-2 p-0" width="40" height="40" style="max-width: none;"') !!}</div>
                                                <div class="col-11">
                                                    <p class="m-0">{{ (new \App\Services\PartyService($email->creator))->getName(true) }}
                                                        &nbsp; {!! $email->Status->badge() !!}
                                                        @if(is_null($email->Dated))
                                                            <span
                                                                class='float-end'>{{ $email->CreatedOn->format('M d, Y H:i') }}</span>
                                                        @else
                                                            <span
                                                                class='float-end'>{{ $email->Dated?->format('M d, Y H:i') }}</span>
                                                        @endif
                                                    </p>
                                                    <p class="m-0">{{ $email->Subject }} </p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div id="Content{{ $email->EmailID }}"
                                     class="accordion-collapse collapse {{--@if($loop->first) show @endif--}}"
                                     aria-labelledby="{{ $email->EmailID }}" data-bs-parent="#MailConversation">
                                    <div class="py-1 border-bottom px-2">
                                        actions:
                                        <button
                                            class="btn btn-sm btn-outline-danger btn-pill float-end mx-2 trash-email"
                                            type="button"
                                            data-route="{{ route('emails.destroy', [$email->EmailID]) }}"><i
                                                class="fas fa-trash-alt"></i> trash
                                        </button>

                                        @if($email->Type->value === \App\Enums\EmailTypeEnum::Incoming->value)
                                            <button
                                                class="btn btn-sm btn-outline-primary btn-pill float-end mx-2 reply-mail-to-action"
                                                data-info="{{ route('email.reply-draft', [$email->EmailID]) }}"
                                                    type="button"><i class="fas fa-reply"></i> reply
                                            </button>
                                        @elseif($email->Status->value === \App\Enums\EmailStatusEnum::Draft->value)
                                            <button
                                                class="btn btn-sm btn-outline-primary btn-pill float-end mx-2 edit-draft-action"
                                                data-info="{{ $email->EmailID }}"
                                                data-route="{{ route('emails.draft-edit',[$email->EmailID]) }}"
                                                type="button"><i class="fas fa-edit"></i> continue editing
                                            </button>
                                        @endif
                                    </div>
                                    <div class="clearfix"></div>
                                    <div class="accordion-body">
                                        {!! $email->Body !!}
                                    </div>
                                    <div class="border-top p-2">
                                        @foreach($email->attachments()->get(['t_CRMImages.ImageID','MIMEType','Name']) as $document)
                                            <span class="btn btn-outline-info modal-preview-document"
                                                  title="{{ $document->Name }}"
                                                  data-url="{{ route('documents.show',[$document->ImageID]) }}"
                                                  id="document-{{ $document->ImageID }}">
                                                    {!! $document->ext()?->getIcon() !!} {{ \Illuminate\Support\Str::limit(explode(".",$document->Name)[0],10,'...') }} {!! $document->ext()?->value !!}
                                                </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header pb-0">
                    <h5 class="card-title ">
                        Users & Teams
                        <span class="float-end">
                            <button type="button" class="btn btn-primary add-watcher-btn">
                            <i class="align-middle" data-feather="share-2"></i> share
                        </button>
                        <button class="btn btn-link float-end" type="button" onclick="fetchWatchersTable()"><i
                                class="fas fa-refresh"></i></button>
                        </span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="float-end">

                    </div>
                    <div class="clearfix mb-2"></div>
                    <table id="conversationWatchersTable"
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
        <div class="col-md-5 col-lg-4">
            <div class="card">
                <div class="card-body">
                    @if($party instanceof \App\Models\BR\Client)
                        @include('snippets.client_summary', ['client'=>$party,'show_summary'=>true])
                        @php
                            $ticket = $party->tickets()->where('Source', \App\Models\CrmEmail::getPrimaryKey())->whereIn('SourceID',$conversation->emails()->select('t_CRMEmails.EmailID'))->first()
                        @endphp
                        @if($ticket instanceof \App\Models\Ticket)
                            <hr>
                            <h4 class="text-center">Ticket  : <a href="javascript:void(0)" data-click_url="{{ route('tickets.edit',[$ticket->TicketID]) }}" data-summary_title="Ticket {{ $ticket->TicketID }} summary" class="click-summary-data"> {{ $ticket->TicketID }}</a></h4>
                        @else
                            <div class="mt-1 border-top border-1 py-3">
                                <div class="row">
                                    <div class="col-12">
                                        <button class="btn btn-primary add-party-ticket-btn w-100" type="button"
                                                data-action="{{ route('client-tickets.store',[$party->ClientID]) }}">
                                            <i class="align-middle" data-feather="check-square"></i> add a ticket
                                        </button>
                                    </div>
                                </div>
                            </div>
                    @endif

                    @elseif($party instanceof \App\Models\Lead)
                        @include('snippets.lead_summary', ['lead'=>$party, 'show_summary'=>true])
                        @php
                            $ticket = $party->tickets()->where('Source', \App\Models\CrmEmail::getPrimaryKey())->whereIn('SourceID',$conversation->emails()->select('t_CRMEmails.EmailID'))->first()
                        @endphp
                        @if($ticket instanceof \App\Models\Ticket)
                            <hr>
                            <h4 class="text-center">Ticket  : <a href="javascript:void(0)" data-click_url="{{ route('tickets.edit',[$ticket->TicketID]) }}" data-summary_title="Ticket {{ $ticket->TicketID }} summary" class="click-summary-data"> {{ $ticket->TicketID }}</a></h4>
                        @else
                        <div class="mt-1 border-top border-1 py-3">
                            <div class="row">
                                <div class="col-12">
                                    <button class="btn btn-primary add-party-ticket-btn w-100" type="button"
                                            data-action="{{ route('lead-tickets.store',[$party->LeadID]) }}">
                                        <i class="align-middle" data-feather="check-square"></i> add a ticket
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif
                    @elseif($party instanceof \App\Models\User)
                        @include('snippets.user_summary', ['user'=>$party, 'show_summary'=>true])
                    @else
                        <h3>{{ (new \App\Services\CRMEmailService($conversation->email))->getParty() }}</h3>

                        <div class="mt-1 border-top border-1 py-3">
                            <div class="row">
                                <div class="col-6">
                                    <div class="btn-group w-100">
                                        <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Lead
                                        </button>
                                        <div class="dropdown-menu" style="">
                                            <a class="dropdown-item  click-summary-data" href="javascript:void(0)"
                                               data-click_url="{{ route('leads.create',['type'=>\App\Enums\LeadTypeEnum::Individual->name,'conversation'=>$conversation->Id]) }}"
                                               data-summary_title="Add Individual Lead"
                                                 ><i class="fas fa-plus-circle"></i> New Individual Lead</a>
                                            <a class="dropdown-item  click-summary-data" href="javascript:void(0)"
                                               data-click_url="{{ route('leads.create',['type'=>\App\Enums\LeadTypeEnum::Company->name, 'conversation'=>$conversation->Id]) }}"
                                               data-summary_title="Add Corporate Lead"
                                                ><i class="fas fa-plus-circle"></i> New Corporate Lead</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="javascript:void(0)" id="triggerLeadContactBtn"><i class="fas fa-address-card"></i> Lead Contact</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="btn-group w-100">
                                        <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Member
                                        </button>
                                        <div class="dropdown-menu" style="">
                                            <a class="dropdown-item" href="javascript:void(0)" id="triggerClientContactBtn"><i class="fas fa-address-card"></i> Member Contact</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="emailConversationModel" tabindex="-1" role="dialog" aria-hidden="true"
         data-bs-backdrop="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item" id="addClientContactModal">
                        <form action="" method="post" id="addClientContactForm" class="row">
                            <input type="hidden" name="conversation" class="d-none" value="{{ $conversation->Id }}">
                            @csrf
                            <div class="mb-3">
                                <label for="client" class="form-label">Member <span class="text-danger">*</span></label>
                                <select class="form-control clients" name="client" id="client" required></select>
                                <p id="client_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="contact_label">Label <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="contact_label" name="contact_label" required placeholder="Email 2">
                                <p id="contact_label_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="contact_email">Email </label>
                                <input type="text" class="form-control" id="contact_email" name="contact_email"
                                       placeholder="contact email" readonly value="{{ (new \App\Services\CRMEmailService($conversation->email))->getParty() }}">
                                <p id="contact_email_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end disabled" id="addClientContactBtn" type="submit">
                                    <i class="fas fa-save"></i> add contact to member
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item" id="addLeadContactModal">
                        <form action="" method="post" id="addLeadContactForm" class="row">
                            <input type="hidden" name="conversation" class="d-none" value="{{ $conversation->Id }}">
                            @csrf
                            <div class="mb-3">
                                <label for="lead" class="form-label">Lead <span class="text-danger">*</span></label>
                                <select class="form-control leads" name="lead" id="lead" required></select>
                                <p id="lead_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="contact_label">Label <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="contact_label" name="contact_label" required placeholder="Email 2">
                                <p id="contact_label_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="contact_email">Email </label>
                                <input type="text" class="form-control" id="contact_email" name="contact_email"
                                       placeholder="contact email" readonly value="{{ (new \App\Services\CRMEmailService($conversation->email))->getParty() }}">
                                <p id="contact_email_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end disabled" id="addLeadContactBtn" type="submit">
                                    <i class="fas fa-save"></i> add contact to lead
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content text-center with-gradient d-none modal-item" id="trashConversationWatcherModal">
                        <h3 class="h3 text-danger">Remove <b id="trashConversationWatcher"></b>
                            from Conversation
                        </h3>
                        <div class="mt-2 mb-2">
                            Are you sure you want to remove this user/team ?
                        </div>
                        <hr>
                        <form id="trashConversationWatcherForm" method="post"> @csrf
                            <div class="mt-4">@method('delete')
                                <button type="button" class="btn btn-success float-start"
                                        data-bs-dismiss="modal">
                                    no, keep
                                </button>
                                <button class="btn btn-danger float-end" id="trashConversationWatcherBtn"
                                        type="submit"><i
                                        class="fas fa-trash"></i> yes, remove
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item" id="addConversationWatcherModal">
                        <form action="{{ route('conversation-watchers.store',[$conversation->Id]) }}" method="post"
                              id="addConversationWatcherForm">
                            @csrf
                            <div class="mb-3">
                                <label for="share_role" class="form-label">Role <span
                                        class="text-danger">*</span></label>
                                <select class="form-control " name="share_role" id="share_role" required>
                                    <option selected disabled>select a role.</option>
                                    @foreach(\App\Enums\Core\RoleEnum::getAll() as $role)
                                        <option value="{{ $role->value }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                <p id="share_role_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label for="share_party" class="form-label">User/Team </label>
                                <select class="form-control" name="share_party" id="share_party" required>
                                </select>
                                <p id="share_party_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-info float-end" id="addConversationWatcherBtn" type="submit"><i
                                        class="fas fa-share-alt"></i> share
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
    <script src="{{ asset('assets/plugins/summernote/summernote-bs5.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/datatables.js') }}"></script>
    @include('snippets.actions.preview-files')
    @include('snippets.actions.tickets',['source'=>\App\Enums\TicketSourceEnum::Email,'hidden'=> '<input type="hidden" name="conversation" class="d-none" value="'. $conversation->Id.'">', 'content' => $conversation->email?->Body])
    <script>const $Modal = $('#emailConversationModel');

        $(function () {
            $('#share_party').select2({
                placeholder: "Search a user or team (t:)", minimumInputLength: 2,
                dropdownParent: $Modal,
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
            $(document).on('click', '.add-watcher-btn', function () {
                $(".modal-item").addClass('d-none');
                $('#addConversationWatcherModal').removeClass('d-none');
                $('.modal-title').html('<b>Share</b> Conversation ');
                $Modal.children().first().removeClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#addConversationWatcherForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#addConversationWatcherBtn'), false, true, true)) {
                    fetchWatchersTable();
                    $Modal.modal('hide');
                }
            });


            $(document).on('click', '.conversation-watchers-trash', function () {
                const name = $(this).data('info');
                $(".modal-item").addClass('d-none');
                $("#trashConversationWatcherForm").attr('action', $(this).data('click_url'));
                $('#trashConversationWatcher').html(name);
                $('#trashConversationWatcherModal').removeClass('d-none');
                $('.modal-title').html('<b>Remove</b> user :' + name+' from conversation');
                $Modal.children().first().removeClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#trashConversationWatcherForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#trashConversationWatcherBtn'), false, true, true)) {
                    fetchWatchersTable();
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.edit-draft-action', function () {
                let id = $(this).data('info');
                let url = $(this).data('route');

                $(this).addClass('disabled').html('<i class="fas fa-spinner fa-spin"></i> please wait');
                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function (data) {
                        $("#DraftEmailContent").html(data).removeClass('d-none');
                        $("#" + id).parent().fadeOutAndRemove('slow');
                    },
                    error: function () {
                        nError('Failed to load content.');
                    }
                });
            });
            $(document).on('click', '.reply-mail-to-action', function () {
                const btn = $(this), url = $(this).data('info');
                console.log(btn);
                btn.addClass('disabled').html('<i class="fas fa-spinner fa-spin"></i> please wait');
                console.log(btn);

                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'html',
                    data: [{name: '_token', value: window.csrf_token}],
                    success: function (data) {
                        $("#DraftEmailContent").html(data).removeClass('d-none');
                    }, error: function () {
                        nError('could not load content');
                    }
                });
                btn.removeClass('disabled').html('<i class="fas fa-reply"></i> reply');
            });

            $(document).on('click', '.trash-email', function () {
                const btn = $(this), btnContent = btn.html(), Url = btn.data('route');
                $(this).prop('disable', true).addClass('disabled').prop('type', 'button').html('<i class="fas fa-spinner fa-spin"></i> please wait');
                window.isDirty = true;
                $.ajax({
                    url: Url,
                    method: 'delete',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Add CSRF token for security
                    },
                    success: function (response) {
                        $("#" + response.email_id).parent().fadeOutAndRemove('slow');
                        nSuccess(response.message);
                        if (response.conversation) {
                            setTimeout(function () {
                                if (response.route) {
                                    window.location.href = response.route;
                                }
                            }, 3000);
                        }
                    },
                    error: function (xhr) {
                        nError(xhr.responseJSON.message);
                        btn.prop('disable', false).removeClass('disabled').prop('type', 'button').html(btnContent);
                    }
                });
                window.isDirty = false;
            });

            $('#client').select2({
                placeholder: "Choose a client...", minimumInputLength: 2,
                dropdownParent: $Modal,
                ajax: {
                    url: '{{route('clients.select2')}}',
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
            }).on('select2:select', function (e) {
                const client_id = e.params.data.id;
                if (typeof client_id === 'string'){
                    $("#addClientContactForm").attr('action', '{{route('clients.index')}}/'+client_id+'/client-contacts');
                    $("#addClientContactBtn").removeClass('disabled');
                }else{
                    $("#addClientContactBtn").addClass('disabled');
                }
            });

            $(document).on('click', '#triggerClientContactBtn', function () {
                $(".modal-item").addClass('d-none');
                $('#addClientContactModal').removeClass('d-none');
                $('.modal-title').html('<b>Create & Attach </b> Conversation to Member');
                $Modal.children().first().removeClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#addClientContactForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#addClientContactBtn'), false, true, true)) {
                    emailPageRefresh();
                    $Modal.modal('hide');
                }
            });

            $('#lead').select2({
                placeholder: "Choose a lead...", minimumInputLength: 2,
                dropdownParent: $Modal,
                ajax: {
                    url: '{{route('leads.select2')}}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {q: $.trim(params.term)};
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {text: item.Name, id: item.LeadID}
                            })
                        };
                    },
                    cache: true
                }
            }).on('select2:select', function (e) {
                const lead_id = e.params.data.id;
                if (Number.isInteger(lead_id)){
                    $("#addLeadContactForm").attr('action', '{{route('leads.index')}}/'+lead_id+'/lead-contacts');
                    $("#addLeadContactBtn").removeClass('disabled');
                }else{
                    $("#addLeadContactBtn").addClass('disabled');
                }
            });

            $(document).on('click', '#triggerLeadContactBtn', function () {
                $(".modal-item").addClass('d-none');
                $('#addLeadContactModal').removeClass('d-none');
                $('.modal-title').html('<b>Create & Attach </b> Conversation to Lead');
                $Modal.children().first().removeClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#addLeadContactForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#addLeadContactBtn'), false, true, true)) {
                    $Modal.modal('hide');
                    emailPageRefresh();
                }
            });


            const $firstAccordion = $('#MailConversation .accordion-item:first');
            const firstEmailId = $firstAccordion.find('.accordion-header').attr('id');

            if ($firstAccordion.find('.fw-bold').length > 0) {
                markEmailAsRead(firstEmailId);
            }

            $('#MailConversation').on('shown.bs.collapse', function (event) {
                const $targetItem = $(event.target);
                const emailId = $targetItem.attr('aria-labelledby');
                const $header = $(`#${emailId}`);

                // Check if the email is unread (has fw-bold class)
                const $unreadIndicator = $header.find('.fw-bold');
                if ($unreadIndicator.length > 0) {
                    markEmailAsRead(emailId);
                }
            });

            fetchWatchersTable();
        });

        function emailPageRefresh() {
            setTimeout(function () {
                window.location.reload();
            }, 3000);
        }
        function markEmailAsRead(emailId) {
            $.ajax({
                url: "{{ route('emails.index') }}/" + emailId,
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Add CSRF token for security
                },
                success: function (response) {
                    $("#" + response.email_id).find('.fw-bold').removeClass('fw-bold');
                },
                error: function (xhr, status, error) {
                }
            });
        }

        function fetchWatchersTable() {
            if (!$.fn.DataTable.isDataTable('#conversationWatchersTable')) {
                $('#conversationWatchersTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[3, 'desc']],
                    ajax: {
                        url: '{{ route('conversation-watchers.index',[$conversation->Id]) }}',
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
                        "sEmptyTable": "conversation does not have any shares under filter"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading conversation watchers.");
                    // console.log(er);
                });
            } else {
                $('#conversationWatchersTable').DataTable().ajax.reload();
            }
        }
    </script>
@endsection
