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
<div class="card">
    <div class="card-header">
        <div class="card-actions float-end">
            <a href="javascript:void(0)" onclick="closeEmailDetails()">
                <i class="fas fa-close"></i>
            </a>
        </div>
        <h5 class="card-title mb-0">{{ $conversation->email?->Subject }}</h5>
    </div>
    <div class="card-body p-1">
        <div class="accordion accordion-flush" id="MailConversation">
            @foreach($conversation->emails()->latest('t_CRMEmails.ModifiedOn')->get() as $email)
                <div class="accordion-item m-1">
                    <div class="accordion-header row" id="{{ $email->EmailID }}">
                        <div class="col-11 p-0">
                            <div class="accordion-button @if(!$loop->first) collapsed @endif " type="button"
                                 data-bs-toggle="collapse" data-bs-target="#Content{{ $email->EmailID }}"
                                 aria-expanded="@if($loop->first) true @else false @endif"
                                 aria-controls="Content{{ $email->EmailID }}">
                                @if($email->Type->value === \App\Enums\EmailTypeEnum::Incoming->value)
                                    <div class="row">
                                        <div
                                            class="col-1 p-0">{!! (new \App\Services\PartyService($email->party))->getImage('class="img-thumbnail me-2 p-0" width="40" height="40" style="max-width: none;"') !!}</div>
                                        <div class="col-11">
                                            <p class="m-0"> {!! (new \App\Services\PartyService($email->party))->simplified(true,true, $email->From) !!}
                                                <span class='float-end'>{{ $email->Dated->format('M d, Y H:i') }}</span>
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
                        <div class="col-1 p-0">
                            <div class="btn-group">
                                <button type="button" class="btn btn-link dropdown-toggle p-0" data-bs-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu" style="">
                                    <a href="javascript:void(0)"
                                       data-info="{{$email->From }}~{{ $email->Subject }}~'{{ $email->Body }}'~{{ $email->EmailID }}"
                                       class="btn btn-lg btn-link me-1 my-1 reply-mail-to-action">
                                        <i class="fas fa-reply"></i> Reply </a>
                                    <a class="dropdown-item" href="javascript:void(0)"><i class="fas fa-eye"></i>
                                        Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="Content{{ $email->EmailID }}"
                         class="accordion-collapse collapse @if($loop->first) show @endif"
                         aria-labelledby="{{ $email->EmailID }}" data-bs-parent="#MailConversation">
                        <div class="accordion-body">
                            {!! $email->Body !!}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@include('snippets.actions.mailto')
<script>
    $(function () {

    });
</script>
