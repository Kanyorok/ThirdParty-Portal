@extends('layouts.app')

@php
    $totalEarnings = $exit->terminalDues->where('IsEarning', true)->sum('Amount');
    $totalDeductions = $exit->terminalDues->where('IsEarning', false)->sum('Amount');
    $netTotal = $totalEarnings - $totalDeductions;
@endphp

@section('title', 'Exit Request')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Exit {{ $exit->ExitNo }}</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.exit.requests.index') }}">Back</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <strong>Employee:</strong>
                    <div>{{ $exit->employee?->FirstName }} {{ $exit->employee?->LastName }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Exit Type:</strong>
                    <div>{{ $exit->exitType?->Name ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Status:</strong>
                    <div>{{ $exit->Status }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Approval:</strong>
                    <div>{{ $exit->ApprovalStatus ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Initiator:</strong>
                    <div>{{ $exit->InitiatorType ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Policy:</strong>
                    <div>{{ $exit->policy?->Name ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Requested On:</strong>
                    <div>{{ $exit->RequestedOn?->format('Y-m-d') ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Notice Date:</strong>
                    <div>{{ $exit->NoticeDate?->format('Y-m-d') ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Effective Exit Date:</strong>
                    <div>{{ $exit->EffectiveExitDate?->format('Y-m-d') ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Notice Days:</strong>
                    <div>{{ $exit->NoticeDays ?? 0 }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Notice Pay In Lieu:</strong>
                    <div>{{ $exit->NoticePayInLieu ? 'Yes' : 'No' }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Notice Pay Amount:</strong>
                    <div>{{ $exit->NoticePayAmount ? number_format($exit->NoticePayAmount, 2) : '-' }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Case:</strong>
                    <div>{{ $exit->case?->CaseNo ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Redundancy:</strong>
                    <div>{{ $exit->redundancy?->RefNo ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Final Payroll:</strong>
                    <div>{{ $exit->FinalPayrollStatus ?? '-' }}</div>
                </div>
            </div>

            @php($initiator = strtolower($exit->InitiatorType ?? ''))
            <div class="mt-3 d-flex flex-wrap gap-2 align-items-center">
                @if($initiator === 'employer')
                    <a class="btn btn-outline-primary" href="{{ route('hr.exit.requests.notice.create', $exit->Id) }}">Notice</a>
                @else
                    <span class="text-muted small">Notice not required for employee-initiated exits.</span>
                @endif
                <a class="btn btn-outline-primary" href="{{ route('hr.exit.requests.clearances', $exit->Id) }}">Clearances</a>
                <a class="btn btn-outline-primary" href="{{ route('hr.exit.terminal-dues.edit', $exit->Id) }}">Terminal Dues</a>
                <a class="btn btn-outline-primary" href="{{ route('hr.exit.interviews.edit', $exit->Id) }}">Exit Interview</a>
            </div>

            <div class="mt-3 d-flex flex-wrap gap-2">
                @if($exit->Status === 'Draft')
                    <form action="{{ route('hr.exit.requests.submit', $exit->Id) }}" method="POST">
                        @csrf
                        <button class="btn btn-outline-secondary" type="submit">Submit</button>
                    </form>
                @endif
                @if($exit->ApprovalStatus !== 'Approved')
                    <form action="{{ route('hr.exit.requests.approve', $exit->Id) }}" method="POST">
                        @csrf
                        <button class="btn btn-outline-success" type="submit">Approve</button>
                    </form>
                @endif
                @if($exit->ApprovalStatus !== 'Rejected')
                    <form action="{{ route('hr.exit.requests.reject', $exit->Id) }}" method="POST">
                        @csrf
                        <button class="btn btn-outline-danger" type="submit">Reject</button>
                    </form>
                @endif
                @if($exit->Status !== 'Closed')
                    <form action="{{ route('hr.exit.requests.close', $exit->Id) }}" method="POST" onsubmit="return confirm('Close this exit request?');">
                        @csrf
                        <button class="btn btn-outline-dark" type="submit">Close</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="mb-3">Notices</h5>
                    <ul class="list-unstyled mb-0">
                        @forelse($exit->notices as $notice)
                            <li class="mb-2">
                                <div>
                                    <strong>{{ $notice->NoticeType }}</strong>
                                    <span class="text-muted">({{ $notice->Status }})</span>
                                </div>
                                <div class="text-muted small">
                                    Issued: {{ $notice->IssuedOn?->format('Y-m-d') ?? '-' }} | Delivery: {{ $notice->DeliveryStatus ?? 'Draft' }}
                                </div>
                                @if($notice->document)
                                    <a href="{{ route('file.preview', ['document' => $notice->document->DocumentId]) }}" target="_blank">
                                        View document
                                    </a>
                                @endif
                            </li>
                        @empty
                            <li class="text-muted">No notices recorded.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Documents</h5>
                    <ul class="list-unstyled">
                        @forelse($exit->documents as $doc)
                            <li>
                                @if($doc->document)
                                    <a href="{{ route('file.preview', ['document' => $doc->document->DocumentId]) }}" target="_blank">
                                        {{ $doc->DocType ?? $doc->document->Name }}
                                    </a>
                                @else
                                    {{ $doc->DocType ?? 'Document' }}
                                @endif
                            </li>
                        @empty
                            <li class="text-muted">No documents uploaded.</li>
                        @endforelse
                    </ul>

                    <form action="{{ route('hr.exit.requests.documents.store', $exit->Id) }}" method="POST" enctype="multipart/form-data" class="mt-3">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="file" name="Document" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="DocType" class="form-control" placeholder="Document type">
                            </div>
                            <div class="col-12">
                                <button class="btn btn-outline-primary">Upload Document</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Clearances</h5>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.exit.requests.clearances', $exit->Id) }}">Manage</a>
                    </div>
                    <ul class="list-unstyled mb-0">
                        @forelse($exit->clearances as $clearance)
                            <li class="mb-1">
                                {{ $clearance->department?->Name ?? 'Department' }} - {{ $clearance->Status }}
                            </li>
                        @empty
                            <li class="text-muted">No clearances recorded.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Terminal Dues</h5>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.exit.terminal-dues.edit', $exit->Id) }}">Manage</a>
                    </div>
                    <div class="small text-muted">Earnings: {{ number_format($totalEarnings ?? 0, 2) }} | Deductions: {{ number_format($totalDeductions ?? 0, 2) }}</div>
                    <div class="fw-bold">Net: {{ number_format($netTotal ?? 0, 2) }}</div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Exit Interview</h5>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.exit.interviews.edit', $exit->Id) }}">Edit</a>
                    </div>
                    @php($interview = $exit->interviews->first())
                    @if($interview)
                        <div><strong>Date:</strong> {{ $interview->InterviewDate?->format('Y-m-d') ?? '-' }}</div>
                        <div><strong>Interviewer:</strong> {{ $interview->interviewer?->FirstName }} {{ $interview->interviewer?->LastName }}</div>
                        <div><strong>Mode:</strong> {{ $interview->Mode ?? '-' }}</div>
                        <div><strong>Reason:</strong> {{ $interview->AttritionReason ?? '-' }}</div>
                    @else
                        <div class="text-muted">No exit interview captured.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-3">
        <div class="card-body">
            <h5 class="mb-3">Status Log</h5>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>From</th>
                            <th>To</th>
                            <th>Remarks</th>
                            <th>Changed On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exit->statusLogs as $log)
                            <tr>
                                <td>{{ $log->FromStatus ?? '-' }}</td>
                                <td>{{ $log->ToStatus }}</td>
                                <td>{{ $log->Remarks ?? '-' }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($log->ChangedOn)->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">No status changes yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
