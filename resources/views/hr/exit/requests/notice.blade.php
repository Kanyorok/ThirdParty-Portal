@extends('layouts.app')

@section('title', 'Exit Notice')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Exit Notice</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.exit.requests.show', $exit->Id) }}">Back</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="mb-3">
                <strong>Employee:</strong> {{ $exit->employee?->FirstName }} {{ $exit->employee?->LastName }} ({{ $exit->employee?->EmployeeNo }})
                <span class="ms-3"><strong>Exit Type:</strong> {{ $exit->exitType?->Name ?? '-' }}</span>
                @if($exit->case)
                    <span class="ms-3"><strong>Case:</strong> {{ $exit->case?->CaseNo }}</span>
                @endif
            </div>
            <form action="{{ route('hr.exit.requests.notice.store', $exit->Id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @php
                    $defaultType = old('NoticeType', $notice->NoticeType ?? ($mappings->first()?->LetterType ?? 'ExitNotice'));
                    $deliveryMethod = old('DeliveryMethod', $notice->DeliveryMethod ?? 'Auto');
                @endphp
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Notice Type *</label>
                        <input type="text" name="NoticeType" class="form-control" value="{{ $defaultType }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Delivery Method *</label>
                        <select name="DeliveryMethod" class="form-select" required>
                            <option value="Auto" @selected($deliveryMethod === 'Auto')>Auto (Generate from Template)</option>
                            <option value="Upload" @selected($deliveryMethod === 'Upload')>Upload Signed Document</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Notice Document</label>
                        <input type="file" name="NoticeDocument" class="form-control">
                        @if($notice?->document)
                            <small class="text-muted d-block mt-1">
                                Current: <a href="{{ route('file.preview', ['document' => $notice->document->DocumentId]) }}" target="_blank">{{ $notice->document->Name }}</a>
                            </small>
                        @endif
                        @if($notice?->document && $deliveryMethod === 'Auto')
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="Regenerate" id="regenerateNotice" value="1">
                                <label class="form-check-label" for="regenerateNotice">Regenerate document</label>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Template</label>
                        <select name="TemplateID" class="form-select">
                            <option value="">Select</option>
                            @foreach($legalTemplates as $template)
                                <option value="{{ $template->Id }}" @selected(old('TemplateID', $notice->TemplateID ?? null) == $template->Id)>
                                    {{ $template->Title }}
                                </option>
                            @endforeach
                        </select>
                        @if($mappings->count())
                            <small class="text-muted d-block mt-1">
                                Mapped templates:
                                @foreach($mappings as $mapping)
                                    <span class="me-2">{{ $mapping->LetterType }}{{ $mapping->TemplateID ? ' ('.$mapping->TemplateID.')' : '' }}</span>
                                @endforeach
                            </small>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Issued On</label>
                        <input type="date" name="IssuedOn" class="form-control" value="{{ old('IssuedOn', $notice?->IssuedOn?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Response Due</label>
                        <input type="date" name="ResponseDue" class="form-control" value="{{ old('ResponseDue', $notice?->ResponseDue?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <input type="text" name="Status" class="form-control" value="{{ old('Status', $notice->Status ?? 'Issued') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Delivery Status</label>
                        <input type="text" class="form-control" value="{{ $notice->DeliveryStatus ?? 'Draft' }}" disabled>
                        @if($notice?->SentOn)
                            <small class="text-muted d-block mt-1">Sent on {{ $notice->SentOn->format('Y-m-d H:i') }}</small>
                        @endif
                    </div>
                    <div class="col-12">
                        <label class="form-label">Summary</label>
                        <textarea name="Summary" class="form-control" rows="4">{{ old('Summary', $notice->Summary ?? '') }}</textarea>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="SendNow" id="sendNow" value="1" @checked(old('SendNow'))>
                            <label class="form-check-label" for="sendNow">Send to employee by email now</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('hr.exit.requests.show', $exit->Id) }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Notice</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
