@php use Carbon\Carbon; @endphp
@php use App\Enums\Core\IntegrationsEnum; @endphp
@php use EchoLabs\Prism\Enums\Provider; @endphp
@extends('layouts.app')

@section('title','Integrations Settings')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="javascript:void(0);">Settings</a></li>
@endsection

@section('styles')

@endsection
@section('content')
    <div class="row">
        <div class="col-md-3 col-xl-2">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">@yield('title')</h5>
                </div>
                <div class="list-group list-group-flush" role="tablist">
                    @foreach(IntegrationsEnum::getAll() as $integration)
                        <a class="list-group-item list-group-item-action {{ ($loop->first)?'active':'' }}"
                           data-bs-toggle="list" href="#{{ $integration->value }}"
                           role="tab">
                            {{ $integration->description() }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-md-9 col-xl-10">
            <div class="tab-content">
                @foreach(IntegrationsEnum::getAll() as $integration)
                    <div class="tab-pane fade {{ ($loop->first)?'show active':'' }}" id="{{ $integration->value }}"
                         role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">{{ $integration->description() }}
                                    @if($integration->value === IntegrationsEnum::Facebook->value)
                                        <span
                                            class="text-muted float-end text-decoration-underline text-primary click-summary-data"
                                            data-click_url="{{ route('help') }}?help=integration_fb"
                                            data-summary_title="Facebook Integration Help ?"
                                            style="cursor: pointer;">Help ?</span>
                                    @endif
                                </h5>
                            </div>
                            <div class="card-body">
                                @switch($integration->value)
                                    @case(IntegrationsEnum::Organization->value)
                                        <form id="orgConfigurationForm" method="post"
                                              action="{{ route('settings.integrations') }}" class="row m-3"> @csrf
                                            <input type="hidden" name="Integration" value="{{ $integration->value }}"
                                                   class="d-none" style="display: none;">
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="Org_Name">Organization Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-org-form"
                                                       id="Org_Name" disabled placeholder="e.g., Acme Corp" required
                                                       value="{{ $orgConfig?->name }}"
                                                       name="Org_Name">
                                                <span id="Org_Name_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="Org_Motto">Motto</label>
                                                <input type="text" class="form-control config-org-form"
                                                       id="Org_Motto" disabled placeholder="e.g., Thinking.Crafting.Transforming"
                                                       value="{{ $orgConfig?->motto }}"
                                                       name="Org_Motto">
                                                <span id="Org_Motto_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="Org_Logo">Logo (data URL)</label>
                                                <input type="text" class="form-control config-org-form"
                                                       id="Org_Logo" disabled placeholder="data:image/png;base64,... or existing path"
                                                       name="Org_Logo">
                                                <span id="Org_Logo_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                                <div class="mt-2">
                                                    <input type="file" class="form-control config-org-form" id="Org_Logo_File" accept="image/*" disabled>
                                                    <small class="text-muted">Select an image to auto-fill the field above.</small>
                                                </div>
                                                @if(isset($orgConfig->logo) && is_string($orgConfig->logo))
                                                    <div class="mt-2">
                                                        <img src="{{ asset($orgConfig->logo) }}" alt="Logo" style="height:48px" class="rounded bg-white p-1 border">
                                                    </div>
                                                @endif
                                            </div>
                                            <hr class="mb-3">
                                            <div class="row">
                                                <div class="col-6">
                                                    <button type="button" class="btn btn-secondary d-none float-start"
                                                            id="orgConfigurationCancelBtn">
                                                        cancel
                                                    </button>
                                                    <button type="button" class="btn btn-primary float-start"
                                                            id="orgConfigurationEditBtn">
                                                        edit config
                                                    </button>
                                                </div>
                                                <div class="col-6">
                                                    <button type="submit" class="btn btn-success d-none float-end"
                                                            id="orgConfigurationBtn">
                                                        save changes
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                        @break
                                    @case(IntegrationsEnum::ReportService->value)
                                        <form id="srsConfigurationForm" method="post"
                                              action="{{ route('settings.integrations') }}" class="row m-3"> @csrf
                                            @if(is_string($srsConfig?->name))
                                                <div class="col-12">
                                                    <h3 class="text-center">User : <span
                                                            class="text-decoration-underline">{{ $srsConfig?->name }}</span>
                                                    </h3>
                                                </div>
                                            @endif
                                            <input type="hidden" name="Integration" value="{{ $integration->value }}"
                                                   class="d-none" style="display: none;">
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="SSRS_Host">Host <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-srs-form"
                                                       id="SSRS_Host" disabled
                                                       placeholder="{{ url('/') }}" required
                                                       value="{{ $srsConfig?->host }}"
                                                       name="SSRS_Host">
                                                <span id="SSRS_Host_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="SSRS_Path">Path<span
                                                        class="text-danger">*</span> </label>
                                                <input type="text" class="form-control config-srs-form"
                                                       id="SSRS_Path" disabled
                                                       placeholder="Reports" required autocomplete="off"
                                                       value="{{ $srsConfig?->path ?? "Reports" }}"
                                                       name="SSRS_Path">
                                                <span id="SSRS_Path_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="SSRS_Username">Username<span
                                                        class="text-danger">*</span> </label>
                                                <input type="text" class="form-control config-srs-form"
                                                       id="SSRS_Username" disabled
                                                       placeholder="SSRS Username" required autocomplete="off"
                                                       name="SSRS_Username">
                                                <span id="SSRS_Username_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="SSRS_Password"> Password<span
                                                        class="text-danger">*</span></label>
                                                <input type="password" class="form-control config-srs-form"
                                                       id="SSRS_Password" disabled
                                                       placeholder="SSRS Password" required autocomplete="off"
                                                       name="SSRS_Password">
                                                <span id="SSRS_Password_error"
                                                      class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <hr class="mb-3">
                                            <div class="row">
                                                <div class="col-6">
                                                    <button type="button" class="btn btn-secondary d-none float-start"
                                                            id="srsConfigurationCancelBtn">
                                                        cancel
                                                    </button>
                                                    <button type="button" class="btn btn-primary float-start"
                                                            id="srsConfigurationEditBtn">
                                                        edit config
                                                    </button>
                                                </div>
                                                <div class="col-6">
                                                    <button type="submit" class="btn btn-success d-none float-end"
                                                            id="srsConfigurationBtn">
                                                        save changes
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                        @break
                                    @case(IntegrationsEnum::Email->value)
                                        <form id="emailConfigurationForm" method="post"
                                              action="{{ route('settings.integrations') }}" class="row"> @csrf
                                            <input type="hidden" name="Integration" value="{{ $integration->value }}"
                                                   class="d-none" style="display: none;">
                                            <h3 class="col-12 mb-0">Incoming</h3>
                                            <hr class="col-12 mt-0">
                                            <div class="mb-3 col-md-4 col-sm-6 col-12">
                                                <label class="form-label" for="Incoming_Server">Server <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-email-form"
                                                       id="Incoming_Server" disabled
                                                       placeholder="Server" required
                                                       value="{{ $emailConfig?->Incoming?->host }}"
                                                       name="Incoming_Server">
                                                <span id="Incoming_Server_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-md-4 col-sm-6 col-12">
                                                <label class="form-label" for="Incoming_Port">Port <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-email-form"
                                                       id="Incoming_Port"
                                                       disabled
                                                       placeholder="Port" required
                                                       value="{{ $emailConfig?->Incoming?->port }}"
                                                       name="Incoming_Port">
                                                <span id="Incoming_Port_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-md-4 col-sm-6 col-12">
                                                <label class="form-label" for="Incoming_Username">Username <span
                                                        class="text-danger">*</span></label>
                                                <input type="email" class="form-control config-email-form"
                                                       id="Incoming_Username" disabled
                                                       placeholder="Username" required
                                                       value="{{ $emailConfig?->Incoming?->username }}"
                                                       name="Incoming_Username">
                                                <span id="Incoming_Username_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-md-4 col-sm-6 col-12">
                                                <label class="form-label" for="Incoming_Password">Password <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-email-form"
                                                       id="Incoming_Password" disabled autocomplete="off"
                                                       placeholder="Password" required
                                                       name="Incoming_Password">
                                                <span id="Incoming_Password_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="col-md-4 col-sm-6 col-12 mb-3">
                                                <label for="" class="form-label">Encryption <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control config-email-form"
                                                        name="Incoming_Encryption"
                                                        disabled id="Incoming_Encryption" required>
                                                    @foreach(App\Enums\EmailEncryptionEnum::getAll() as $encryption)
                                                        <option
                                                            value="{{ $encryption->value }}" {{ ($encryption->value===$emailConfig?->Incoming?->encryption)?'selected' :''}}>{{ $encryption->name }}</option>
                                                    @endforeach
                                                </select>
                                                <p id="Incoming_Encryption_error"
                                                   class="invalid-feedback d-none error col-12" role="alert"></p>
                                            </div>
                                            <h3 class="col-12 mb-0 ">Outgoing</h3>
                                            <hr class="col-12 mt-0">
                                            <div class="mb-3 col-md-4 col-sm-6 col-12">
                                                <label class="form-label" for="Outgoing_Server">Server <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-email-form"
                                                       id="Outgoing_Server" disabled
                                                       placeholder="Server" required
                                                       value="{{ $emailConfig?->Outgoing?->host }}"
                                                       name="Outgoing_Server">
                                                <span id="Outgoing_Server_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-md-4 col-sm-6 col-12">
                                                <label class="form-label" for="Outgoing_Port">Port <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-email-form"
                                                       id="Outgoing_Port"
                                                       disabled
                                                       placeholder="Port" required
                                                       value="{{ $emailConfig?->Outgoing?->port }}"
                                                       name="Outgoing_Port">
                                                <span id="Outgoing_Port_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-md-4 col-sm-6 col-12">
                                                <label class="form-label" for="Outgoing_Username">Username <span
                                                        class="text-danger">*</span></label>
                                                <input type="email" class="form-control config-email-form"
                                                       id="Outgoing_Username" disabled
                                                       placeholder="Username" required
                                                       value="{{ $emailConfig?->Outgoing?->username }}"
                                                       name="Outgoing_Username">
                                                <span id="Outgoing_Username_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-md-4 col-sm-6 col-12">
                                                <label class="form-label" for="Outgoing_Password">Password <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-email-form"
                                                       id="Outgoing_Password" disabled
                                                       placeholder="Password" required autocomplete="off"
                                                       name="Outgoing_Password">
                                                <span id="Outgoing_Password_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="col-md-4 col-sm-6 col-12 mb-3">
                                                <label for="" class="form-label">Encryption <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control config-email-form"
                                                        name="Outgoing_Encryption"
                                                        disabled id="Outgoing_Encryption" required>
                                                    @foreach(App\Enums\EmailEncryptionEnum::getAll() as $encryption)
                                                        <option
                                                            value="{{ $encryption->value }}" {{ ($encryption->value===$emailConfig?->Outgoing?->encryption)?'selected' :''}}>{{ $encryption->name }}</option>
                                                    @endforeach
                                                </select>
                                                <p id="Outgoing_Encryption_error"
                                                   class="invalid-feedback d-none error col-12" role="alert"></p>
                                            </div>
                                            <hr class="mb-3">
                                            <div class="row">
                                                <div class="col-6">
                                                    <button type="button" class="btn btn-secondary d-none float-start"
                                                            id="emailConfigurationCancelBtn">
                                                        cancel
                                                    </button>
                                                    <button type="button" class="btn btn-primary float-start"
                                                            id="emailConfigurationEditBtn">
                                                        edit config
                                                    </button>
                                                </div>
                                                <div class="col-6">
                                                    <button type="submit" class="btn btn-success d-none float-end"
                                                            id="emailConfigurationBtn">
                                                        save changes
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                        @break
                                    @case(IntegrationsEnum::SMS->value)
                                        <form id="smsConfigurationForm" method="post"
                                              action="{{ route('settings.integrations') }}" class="row"> @csrf
                                            <input type="hidden" name="Integration" value="{{ $integration->value }}"
                                                   class="d-none" style="display: none;">
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="SMS_Priority">SMS Priority <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-sms-form"
                                                       id="SMS_Priority" disabled
                                                       placeholder="Client_Id" required
                                                       value="{{ property_exists($smsConfig,'priority')?$smsConfig?->priority:'' }}"
                                                       name="SMS_Priority">
                                                <span id="SMS_Priority_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="SMS_Message_Type">Message Type <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-sms-form"
                                                       id="SMS_Message_Type" disabled
                                                       placeholder="Client_Id" required
                                                       value="{{ property_exists($smsConfig,'messageType')?$smsConfig?->messageType:'' }}"
                                                       name="SMS_Message_Type">
                                                <span id="SMS_Message_Type_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="SMS_Sender_Id">Sender Id / Username <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-sms-form"
                                                       id="SMS_Sender_Id"
                                                       disabled
                                                       placeholder="Sender Id" required
                                                       value="{{ property_exists($smsConfig,'sender_Id')?$smsConfig?->sender_Id:'' }}"
                                                       name="SMS_Sender_Id">
                                                <span id="SMS_Sender_Id_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="SMS_Password">API Key / Password <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-sms-form"
                                                       id="SMS_Password" disabled autocomplete="off"
                                                       placeholder="Password" required
                                                       name="SMS_Password">
                                                <span id="SMS_Password_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <hr class="mb-3">
                                            <div class="row">
                                                <div class="col-6">
                                                    <button type="button" class="btn btn-secondary d-none float-start"
                                                            id="smsConfigurationCancelBtn">
                                                        cancel
                                                    </button>
                                                    <button type="button" class="btn btn-primary float-start"
                                                            id="smsConfigurationEditBtn">
                                                        edit config
                                                    </button>
                                                </div>
                                                <div class="col-6">
                                                    <button type="submit" class="btn btn-success d-none float-end"
                                                            id="smsConfigurationBtn">
                                                        save changes
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                        @break
                                    @case(IntegrationsEnum::InfoBip->value)
                                        <form id="infoBipConfigurationForm" method="post"
                                              action="{{ route('settings.integrations') }}" class="row"> @csrf
                                            <input type="hidden" name="Integration" value="{{ $integration->value }}"
                                                   class="d-none" style="display: none;">
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="InfoBip_Host">Host <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-infoBip-form"
                                                       id="InfoBip_Host" disabled
                                                       placeholder="InfoBip_Host" required
                                                       value="{{ $infoBipConfig?->Host }}"
                                                       name="InfoBip_Host">
                                                <span id="InfoBip_Host_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="InfoBip_Email">Sender Email <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-infoBip-form"
                                                       id="InfoBip_Email"
                                                       disabled
                                                       placeholder="Sender Email" required
                                                       value="{{ $infoBipConfig?->Email }}"
                                                       name="InfoBip_Email">
                                                <span id="InfoBip_Email_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-12">
                                                <label class="form-label" for="InfoBip_API_Key">API Key <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-infoBip-form"
                                                       id="InfoBip_API_Key" disabled autocomplete="off"
                                                       placeholder="API Key" required
                                                       name="InfoBip_API_Key">
                                                <span id="InfoBip_API_Key_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <hr class="mb-3">
                                            <div class="row">
                                                <div class="col-6">
                                                    <button type="button" class="btn btn-secondary d-none float-start"
                                                            id="infoBipConfigurationCancelBtn">
                                                        cancel
                                                    </button>
                                                    <button type="button" class="btn btn-primary float-start"
                                                            id="infoBipConfigurationEditBtn">
                                                        edit config
                                                    </button>
                                                </div>
                                                <div class="col-6">
                                                    <button type="submit" class="btn btn-success d-none float-end"
                                                            id="infoBipConfigurationBtn">
                                                        save changes
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                        @break
                                    @case(IntegrationsEnum::iTrack->value)
                                        <form id="iTrackConfigurationForm" method="post"
                                              action="{{ route('settings.integrations') }}" class="row"> @csrf
                                            <input type="hidden" name="Integration" value="{{ $integration->value }}"
                                                   class="d-none" style="display: none;">
                                            <div class="mb-3 col-12">
                                                <label class="form-label" for="iTrack_URl">Host <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-iTrack-form"
                                                       id="iTrack_URl" disabled
                                                       placeholder="https://www.itrack.top" required
                                                       value="{{ $iTrackConfig?->host }}"
                                                       name="iTrack_URl">
                                                <span id="iTrack_URl_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="iTrack_Username">Username <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-iTrack-form"
                                                       id="iTrack_Username" disabled placeholder="Username" required
                                                       name="iTrack_Username">
                                                <span id="iTrack_Username_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="iTrack_Password">Password <span
                                                        class="text-danger">*</span></label>
                                                <input type="password" class="form-control config-iTrack-form"
                                                       id="iTrack_Password" disabled autocomplete="off"
                                                       placeholder="Password" required
                                                       name="iTrack_Password">
                                                <span id="iTrack_Password_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <hr class="mb-3">
                                            <div class="row">
                                                <div class="col-6">
                                                    <button type="button" class="btn btn-secondary d-none float-start"
                                                            id="iTrackConfigurationCancelBtn">
                                                        cancel
                                                    </button>
                                                    <button type="button" class="btn btn-primary float-start"
                                                            id="iTrackConfigurationEditBtn">
                                                        edit config
                                                    </button>
                                                </div>
                                                <div class="col-6">
                                                    <button type="submit" class="btn btn-success d-none float-end"
                                                            id="iTrackConfigurationBtn">
                                                        save changes
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                        @break
                                    @case(IntegrationsEnum::PBX->value)
                                        <form id="3cxConfigurationForm" method="post"
                                              action="{{ route('settings.integrations') }}" class="row m-3"> @csrf
                                            <input type="hidden" name="Integration" value="{{ $integration->value }}"
                                                   class="d-none" style="display: none;">
                                            <div class="mb-3 col-12">
                                                <label class="form-label" for="PBX_API_Key">API Key </label>
                                                <div class="input-group">
                                                    <input type="text" readonly class="form-control"
                                                           id="PBX_API_Key"
                                                           placeholder="****************************************">
                                                    <button class="btn btn-secondary" type="button"
                                                            onclick="copyFieldText('PBX_API_Key')"><i
                                                            class="fas fa-copy"></i></button>
                                                </div>
                                            </div>
                                            <div class="px-3">
                                                <div class="alert alert-primary" role="alert">
                                                    <div class="alert-message">
                                                        <strong>Note!</strong> generating a new key invalidates the
                                                        current
                                                        key.
                                                    </div>
                                                </div>
                                            </div>

                                            <hr class="mb-1">
                                            <div class="row">
                                                <div class="col-6">
                                                    &nbsp;
                                                </div>
                                                <div class="col-6">
                                                    <button type="submit" class="btn btn-success float-end"
                                                            id="3cxConfigurationBtn">
                                                        generate New key
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                        @break
                                    @case(IntegrationsEnum::CoreBanking->value)
                                        <form id="cbsConfigurationForm" method="post"
                                              action="{{ route('settings.integrations') }}" class="row m-3"> @csrf
                                            <input type="hidden" name="Integration" value="{{ $integration->value }}"
                                                   class="d-none" style="display: none;">
                                            <div class="mb-3 col-12">
                                                <label class="form-label" for="CBS_Host">Host <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-cbs-form"
                                                       id="CBS_Host" disabled
                                                       placeholder="{{ url('/') }}" required
                                                       value="{{ $cbsConfig?->host }}"
                                                       name="CBS_Host">
                                                <span id="CBS_Host_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="CBS_ConsumerKey"> Consumer Key<span
                                                        class="text-danger">*</span> </label>
                                                <input type="text" class="form-control config-cbs-form"
                                                       id="CBS_ConsumerKey" disabled
                                                       placeholder="Consumer Key" required autocomplete="off"
                                                       name="CBS_ConsumerKey">
                                                <span id="CBS_ConsumerKey_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="CBS_ConsumerSecret"> Consumer Secret<span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-cbs-form"
                                                       id="CBS_ConsumerSecret" disabled
                                                       placeholder="Consumer Secret" required autocomplete="off"
                                                       name="CBS_ConsumerSecret">
                                                <span id="CBS_ConsumerSecret_error"
                                                      class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <hr class="mb-3">
                                            <div class="row">
                                                <div class="col-6">
                                                    <button type="button" class="btn btn-secondary d-none float-start"
                                                            id="cbsConfigurationCancelBtn">
                                                        cancel
                                                    </button>
                                                    <button type="button" class="btn btn-primary float-start"
                                                            id="cbsConfigurationEditBtn">
                                                        edit config
                                                    </button>
                                                </div>
                                                <div class="col-6">
                                                    <button type="submit" class="btn btn-success d-none float-end"
                                                            id="cbsConfigurationBtn">
                                                        save changes
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                        @break
                                    @case(IntegrationsEnum::Channels->value)
                                        <form id="channelConfigurationForm" method="post"
                                              action="{{ route('settings.integrations') }}" class="row m-3"> @csrf
                                            <input type="hidden" name="Integration" value="{{ $integration->value }}"
                                                   class="d-none" style="display: none;">
                                            <div class="mb-3 col-12">
                                                <label class="form-label" for="Channel_API_Key">API Key </label>
                                                <div class="input-group">
                                                    <input type="text" readonly class="form-control config-channel-form"
                                                           id="Channel_API_Key"
                                                           placeholder="****************************************">
                                                    <button class="btn btn-secondary" type="button"
                                                            onclick="copyFieldText('Channel_API_Key')"><i
                                                            class="fas fa-copy"></i></button>
                                                </div>
                                            </div>
                                            <div class="mb-3 col-12">
                                                <label class="form-label" for="Channel_Callback">Callback Url <span
                                                        class="text-danger">*</span></label>
                                                <input type="url" class="form-control config-channel-form"
                                                       id="Channel_Callback" disabled
                                                       placeholder="{{ route('home') }}" required
                                                       value="{{ $channelsConfig?->Callback }}"
                                                       name="Channel_Callback">
                                                <span id="Channel_Callback_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="px-3">
                                                <div class="alert alert-primary" role="alert">
                                                    <div class="alert-message">
                                                        <strong>Note!</strong> generating a new key invalidates the
                                                        current
                                                        key.
                                                    </div>
                                                </div>
                                            </div>
                                            <hr class="mb-1">
                                            <div class="row">
                                                <div class="col-6">
                                                    <button type="button" class="btn btn-secondary d-none float-start"
                                                            id="channelConfigurationCancelBtn">
                                                        cancel
                                                    </button>
                                                    <button type="button" class="btn btn-primary float-start"
                                                            id="channelConfigurationEditBtn">
                                                        edit config
                                                    </button>
                                                </div>
                                                <div class="col-6">
                                                    <button type="submit" class="btn btn-success d-none float-end"
                                                            id="channelConfigurationBtn">
                                                        generate New key
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                        @break
                                    @case(IntegrationsEnum::Facebook->value)
                                        <form id="facebookConfigurationForm" method="post"
                                              action="{{ route('settings.integrations') }}" class="row m-3"> @csrf
                                            <input type="hidden" name="Integration" value="{{ $integration->value }}"
                                                   class="d-none" style="display: none;">
                                            @if(is_string($facebookConfig?->page_name))
                                                <div class="col-12">
                                                    <h3 class="text-center">page: <span
                                                            class="text-decoration-underline">{{ $facebookConfig?->page_name }}</span>
                                                    </h3>
                                                </div>
                                            @endif
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="FB_App_Id">APP Id <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-facebook-form"
                                                       id="FB_App_Id" disabled placeholder="APP ID" required
                                                       value="{{ $facebookConfig?->app_id }}"
                                                       name="FB_App_Id">
                                                <span id="FB_App_Id_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="FB_App_Secret">APP Secret <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-facebook-form"
                                                       id="FB_App_Secret" disabled placeholder="APP Secret" required
                                                       name="FB_App_Secret">
                                                <span id="FB_App_Secret_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="FB_Page_Id">Page Id <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-facebook-form"
                                                       id="FB_Page_Id" disabled placeholder="Page ID" required
                                                       value="{{ $facebookConfig?->page_id }}"
                                                       name="FB_Page_Id">
                                                <span id="FB_Page_Id_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="FB_Page_Token">Page Token <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-facebook-form"
                                                       id="FB_Page_Token" disabled placeholder="Page Token" required
                                                       name="FB_Page_Token">
                                                @if(is_numeric($facebookConfig?->page_token_expires_at))
                                                    <p class="error text-info">Expires
                                                        on: {{ Carbon::createFromFormat('U',$facebookConfig?->page_token_expires_at)->format('M d, Y H:i T') }} </p>
                                                @endif
                                                <span id="FB_Page_Token_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>

                                            <hr class="mb-3">
                                            <div class="row">
                                                <div class="col-6">
                                                    <button type="button" class="btn btn-secondary d-none float-start"
                                                            id="facebookConfigurationCancelBtn">
                                                        cancel
                                                    </button>
                                                    <button type="button" class="btn btn-primary float-start"
                                                            id="facebookConfigurationEditBtn">
                                                        edit config
                                                    </button>
                                                </div>
                                                <div class="col-6">
                                                    <button type="submit" class="btn btn-success d-none float-end"
                                                            id="facebookConfigurationBtn">
                                                        save changes
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                        @break
                                    @case(IntegrationsEnum::Twitter->value)
                                        <form id="twitterConfigurationForm" method="post"
                                              action="{{ route('settings.integrations') }}" class="row m-3"> @csrf
                                            <input type="hidden" name="Integration" value="{{ $integration->value }}"
                                                   class="d-none" style="display: none;">
                                            @if(is_string($twitterConfig?->username))
                                                <div class="col-12">
                                                    <h3 class="text-center">@<span
                                                            class="text-decoration-underline">{{ $twitterConfig?->username }}</span>
                                                    </h3>
                                                </div>
                                            @endif
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="X_Access_Token">Access Token <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-twitter-form"
                                                       id="X_Access_Token" disabled placeholder="Access Token" required
                                                       name="X_Access_Token">
                                                <span id="X_Access_Token_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="X_Access_Token_Secret">Access Token
                                                    Secret <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-twitter-form"
                                                       id="X_Access_Token_Secret" disabled
                                                       placeholder="Access Token Secret" required
                                                       name="X_Access_Token_Secret">
                                                <span id="X_Access_Token_Secret_error"
                                                      class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="X_Consumer_Key">Consumer Key <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-twitter-form"
                                                       id="X_Consumer_Key" disabled placeholder="Consumer Key" required
                                                       name="X_Consumer_Key">
                                                <span id="X_Consumer_Key_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="X_Consumer_Secret"> Consumer Secret<span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-twitter-form"
                                                       id="X_Consumer_Secret" disabled placeholder="Consumer Secret"
                                                       required
                                                       name="X_Consumer_Secret">
                                                <span id="X_Consumer_Secret_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="X_Bearer_Token"> Bearer Token<span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-twitter-form"
                                                       id="X_Bearer_Token" disabled placeholder="Bearer Token" required
                                                       name="X_Bearer_Token">
                                                <span id="X_Bearer_Token_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="X_Is_Free"> Free Mode (Non Paid) <span
                                                        class="text-danger">*</span></label>
                                                <select name="X_Is_Free" id="X_Is_Free" disabled
                                                        class="form-control config-twitter-form">
                                                    <option selected value="yes">Yes (default)</option>
                                                    <option value="no">No</option>
                                                </select>
                                                <span id="X_Is_Free_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <hr class="mb-3">
                                            <div class="row">
                                                <div class="col-6">
                                                    <button type="button" class="btn btn-secondary d-none float-start"
                                                            id="twitterConfigurationCancelBtn">
                                                        cancel
                                                    </button>
                                                    <button type="button" class="btn btn-primary float-start"
                                                            id="twitterConfigurationEditBtn">
                                                        edit config
                                                    </button>
                                                </div>
                                                <div class="col-6">
                                                    <button type="submit" class="btn btn-success d-none float-end"
                                                            id="twitterConfigurationBtn">
                                                        save changes
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                        @break
                                    @case(IntegrationsEnum::Website->value)
                                        <form id="websiteConfigurationForm" method="post"
                                              action="{{ route('settings.integrations') }}" class="row m-3"> @csrf
                                            <input type="hidden" name="Integration" value="{{ $integration->value }}"
                                                   class="d-none" style="display: none;">
                                            <div class="mb-3 col-12">
                                                <label class="form-label" for="Website_API_Key">API Key </label>
                                                <div class="input-group">
                                                    <input type="text" readonly class="form-control"
                                                           id="Website_API_Key"
                                                           placeholder="****************************************">
                                                    <button class="btn btn-secondary" type="button"
                                                            onclick="copyFieldText('Website_API_Key')"><i
                                                            class="fas fa-copy"></i></button>
                                                </div>
                                            </div>
                                            <div class="px-3">
                                                <div class="alert alert-primary" role="alert">
                                                    <div class="alert-message">
                                                        <strong>Note!</strong> generating a new key invalidates the
                                                        current
                                                        key.
                                                    </div>
                                                </div>
                                            </div>

                                            <hr class="mb-1">
                                            <div class="row">
                                                <div class="col-6">
                                                    &nbsp;
                                                </div>
                                                <div class="col-6">
                                                    <button type="submit" class="btn btn-success float-end"
                                                            id="websiteConfigurationBtn">
                                                        generate New key
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                        @break
                                    @case(IntegrationsEnum::LLM->value)
                                        <form id="llmConfigurationForm" method="post"
                                              action="{{ route('settings.integrations') }}" class="row m-3"> @csrf
                                            <input type="hidden" name="Integration" value="{{ $integration->value }}"
                                                   class="d-none" style="display: none;">
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="LLM_Provider">Provider <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control  config-llm-form" name="LLM_Provider"
                                                        disabled id="LLM_Provider" required>
                                                    @foreach(Provider::cases() as $case)
                                                        <option
                                                            value="{{ $case->value }}" {{ ($case->value===$llmConfig?->Provider)?'selected' :''}}>{{ $case->name }}</option>
                                                    @endforeach
                                                </select>
                                                <span id="LLM_Provider_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-sm-6 col-12">
                                                <label class="form-label" for="LLM_Model"> Model<span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-llm-form" id="LLM_Model"
                                                       disabled placeholder="Model e.g. gpt-4o-mini "
                                                       required value="{{ $llmConfig?->Model }}"
                                                       name="LLM_Model">
                                                <span id="Model_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <div class="mb-3 col-12">
                                                <label class="form-label" for="LLM_API_Key">API Key <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control config-llm-form"
                                                       id="LLM_API_Key" disabled placeholder="API Key" required
                                                       name="LLM_API_Key">
                                                <span id="LLM_API_Key_error" class="invalid-feedback d-none error"
                                                      role="alert"></span>
                                            </div>
                                            <hr class="mb-3">
                                            <div class="row">
                                                <div class="col-6">
                                                    <button type="button" class="btn btn-secondary d-none float-start"
                                                            id="llmConfigurationCancelBtn">
                                                        cancel
                                                    </button>
                                                    <button type="button" class="btn btn-primary float-start"
                                                            id="llmConfigurationEditBtn">
                                                        edit config
                                                    </button>
                                                </div>
                                                <div class="col-6">
                                                    <button type="submit" class="btn btn-success d-none float-end"
                                                            id="llmConfigurationBtn">
                                                        save changes
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                        @break
                                @endswitch
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(function () {
            $("#orgConfigurationEditBtn").on('click', function () {
                enable('org')
            });
            $("#orgConfigurationCancelBtn").on('click', function () {
                disable('org');
            });
            $('form#orgConfigurationForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $("#orgConfigurationBtn"), false, false, true)) {
                    disable('org');
                }
            });
            $('#Org_Logo_File').on('change', function (e) {
                const file = e.target.files && e.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = function(evt) {
                    const dataUrl = evt.target.result;
                    if (typeof dataUrl === 'string' && dataUrl.startsWith('data:image/')) {
                        $('#Org_Logo').val(dataUrl);
                    }
                };
                reader.readAsDataURL(file);
            });
            $("#emailConfigurationEditBtn").on('click', function () {
                enable('email')
            });
            $("#emailConfigurationCancelBtn").on('click', function () {
                disable('email');
            });
            $('form#emailConfigurationForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $("#emailConfigurationBtn"), false, false, true)) {
                    disable('email');
                }
            });

            $("#llmConfigurationEditBtn").on('click', function () {
                enable('llm')
            });
            $("#llmConfigurationCancelBtn").on('click', function () {
                disable('llm');
            });
            $('form#llmConfigurationForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $("#llmConfigurationBtn"), false, false, true)) {
                    disable('llm');
                }
            });

            $("#twitterConfigurationEditBtn").on('click', function () {
                enable('twitter')
            });
            $("#twitterConfigurationCancelBtn").on('click', function () {
                disable('twitter');
            });
            $('form#twitterConfigurationForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $("#twitterConfigurationBtn"), false, false, true)) {
                    disable('twitter');
                }
            });

            $("#facebookConfigurationEditBtn").on('click', function () {
                enable('facebook')
            });
            $("#facebookConfigurationCancelBtn").on('click', function () {
                disable('facebook');
            });
            $('form#facebookConfigurationForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $("#facebookConfigurationBtn"), false, false, true)) {
                    disable('facebook');
                }
            });

            $("#infoBipConfigurationEditBtn").on('click', function () {
                enable('infoBip')
            });
            $("#infoBipConfigurationCancelBtn").on('click', function () {
                disable('infoBip');
            });
            $('form#infoBipConfigurationForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $("#infoBipConfigurationBtn"), false, false, true)) {
                    disable('infoBip');
                }
            });

            $("#smsConfigurationEditBtn").on('click', function () {
                enable('sms')
            });
            $("#smsConfigurationCancelBtn").on('click', function () {
                disable('sms');
            });
            $('form#smsConfigurationForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $("#smsConfigurationBtn"), false, false, true)) {
                    disable('sms');
                }
            });

            $("#cbsConfigurationEditBtn").on('click', function () {
                enable('cbs')
            });
            $("#cbsConfigurationCancelBtn").on('click', function () {
                disable('cbs');
            });
            $('form#cbsConfigurationForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $("#cbsConfigurationBtn"), false, false, true)) {
                    disable('cbs');
                }
            });

            $("#srsConfigurationEditBtn").on('click', function () {
                enable('srs')
            });
            $("#srsConfigurationCancelBtn").on('click', function () {
                disable('srs');
            });
            $('form#srsConfigurationForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $("#srsConfigurationBtn"), false, false, true)) {
                    disable('srs');
                }
            });

            $("#iTrackConfigurationEditBtn").on('click', function () {
                enable('iTrack')
            });
            $("#iTrackConfigurationCancelBtn").on('click', function () {
                disable('iTrack');
            });
            $('form#iTrackConfigurationForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $("#iTrackConfigurationBtn"), false, false, true)) {
                    disable('iTrack');
                }
            });

            $("#channelConfigurationEditBtn").on('click', function () {
                enable('channel');
            });
            $("#channelConfigurationCancelBtn").on('click', function () {
                disable('channel');
            });
            $('form#channelConfigurationForm').submit(async function (e) {
                e.preventDefault();
                let data = await saveForm($(this), $('#channelConfigurationBtn'), false, true, true)
                if (data) {
                    $('#Channel_API_Key').val(data.token);
                    disable('channel');
                }
            });

            $('form#websiteConfigurationForm').submit(async function (e) {
                e.preventDefault();
                let data = await saveForm($(this), $('#websiteConfigurationBtn'), false, true, true)
                if (data) {
                    $('#Website_API_Key').val(data.token);
                }
            });
            $('form#3cxConfigurationForm').submit(async function (e) {
                e.preventDefault();
                let data = await saveForm($(this), $('#3cxConfigurationBtn'), false, true, true)
                if (data) {
                    $('#PBX_API_Key').val(data.token);
                }
            });
        });

        function disable(integration) {
            $("#" + integration + "ConfigurationCancelBtn").addClass('d-none');
            $("#" + integration + "ConfigurationBtn").addClass('d-none');
            $("#" + integration + "ConfigurationEditBtn").removeClass('d-none');
            $(".config-" + integration + "-form").prop('disabled', true);
        }

        function enable(integration) {
            $("#" + integration + "ConfigurationCancelBtn").removeClass('d-none');
            $("#" + integration + "ConfigurationBtn").removeClass('d-none');
            $("#" + integration + "ConfigurationEditBtn").addClass('d-none');
            $(".config-" + integration + "-form").prop('disabled', false);
        }

        function copyFieldText(ElementId) {
            const copyText = document.getElementById(ElementId);

            copyText.select();
            copyText.setSelectionRange(0, 99999); // For mobile devices

            if (copyText.value === '') {
                nWarning('Generate a key first.');
            } else {
                window.navigator.clipboard.writeText(copyText.value);
                nSuccess('Text copied successfully.');
            }

        }

    </script>
@endsection
