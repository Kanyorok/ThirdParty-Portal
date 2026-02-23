@extends('layouts.app')

@section('title', 'Shared Document')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Shared Document</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.shared-docs.index') }}">Back</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <h5 class="mb-1">{{ $document->Title }}</h5>
                    <div class="text-muted">{{ $document->Description ?? 'No description' }}</div>
                    <div class="mt-3">
                        <div><strong>Category:</strong> {{ $document->category?->Name ?? '-' }}</div>
                        <div><strong>Version:</strong> {{ $document->Version ?? '-' }}</div>
                        <div><strong>Status:</strong> {{ $document->Status }}</div>
                        <div><strong>Language:</strong> {{ $document->Language ?? '-' }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div><strong>Effective:</strong> {{ $document->EffectiveDate?->format('Y-m-d') ?? '-' }}</div>
                    <div><strong>Expiry:</strong> {{ $document->ExpiryDate?->format('Y-m-d') ?? '-' }}</div>
                    <div><strong>Ack Due:</strong> {{ $document->AcknowledgementDueOn?->format('Y-m-d') ?? '-' }}</div>
                    <div><strong>Owner Dept:</strong> {{ $document->ownerDepartment?->Name ?? '-' }}</div>
                    <div><strong>Access:</strong> {{ $document->AccessLevel }}</div>
                    <div><strong>Mandatory:</strong> {{ $document->IsMandatory ? 'Yes' : 'No' }}</div>
                    <div><strong>Downloadable:</strong> {{ $document->IsDownloadable ? 'Yes' : 'No' }}</div>
                </div>
            </div>

            @if($document->document)
                <div class="mt-3">
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('file.preview', ['document' => $document->document->DocumentId]) }}" target="_blank">View Document</a>
                </div>
            @endif

            @if($document->AccessLevel === 'Department')
                <div class="mt-3">
                    <strong>Departments:</strong>
                    {{ $document->departments->pluck('Name')->implode(', ') ?: '-' }}
                </div>
            @endif
            @if($document->AccessLevel === 'Role')
                <div class="mt-2">
                    <strong>Roles:</strong>
                    {{ $document->roles->pluck('Name')->implode(', ') ?: '-' }}
                </div>
            @endif

            @if($document->IsMandatory && (!$myAck || $myAck->Status !== 'Acknowledged'))
                <form method="POST" action="{{ route('hr.shared-docs.acknowledge', $document->Id) }}" class="mt-3">
                    @csrf
                    <button class="btn btn-success" type="submit">Acknowledge</button>
                </form>
            @elseif($myAck)
                <div class="mt-3 text-success">Acknowledged on {{ $myAck->AcknowledgedOn?->format('Y-m-d H:i') ?? '-' }}</div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Acknowledgements</h5>
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Status</th>
                            <th>Due On</th>
                            <th>Acknowledged On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($acknowledgements as $ack)
                            @php
                                $ackStatus = $ack->Status;
                                if ($ackStatus !== 'Acknowledged' && $ack->DueOn && $ack->DueOn->lt(now()->startOfDay())) {
                                    $ackStatus = 'Overdue';
                                }
                            @endphp
                            <tr>
                                <td>{{ $ack->employee?->FirstName }} {{ $ack->employee?->LastName }}</td>
                                <td>{{ $ackStatus }}</td>
                                <td>{{ $ack->DueOn?->format('Y-m-d') ?? '-' }}</td>
                                <td>{{ $ack->AcknowledgedOn?->format('Y-m-d H:i') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No acknowledgements recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
