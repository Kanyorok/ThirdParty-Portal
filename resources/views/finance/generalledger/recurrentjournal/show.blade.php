@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                <!-- Left side -->
                <div>
                    <h5 class="mb-0 text-info">
                        <i class="fas fa-retweet me-2"></i> Recurrent Journal Details
                        <small class="text-muted">#{{ $journalEntry->RefNo }}</small>
                    </h5>
                </div>

                <!-- Right side -->
                <div class="text-end d-flex align-items-center gap-2 no-print">
                    <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                    Approval Status:
                    @if($journalEntry->ApprovalStatus == 'posted')
                        <span class="badge bg-success">Approved</span>
                    @elseif($journalEntry->ApprovalStatus == 'rejected')
                        <span class="badge bg-danger">Rejected</span>
                    @elseif($journalEntry->ApprovalStatus == 'draft')
                        <span class="badge bg-warning text-dark">Pending</span>
                    @endif
                </div>
            </div>

            <div class="card-body">
                {{-- Header Info --}}
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <h6>Recurring Schedule</h6>
                        @if ($journalEntry->recurringJournals->isNotEmpty())
                            @php $recurring = $journalEntry->recurringJournals->first(); @endphp
                            <div class="small">Start: <strong>{{ $recurring->StartDate ? \Carbon\Carbon::parse($recurring->StartDate)->format('d/m/Y') : 'N/A' }}</strong></div>
                            <div class="small">Next Run: <strong>{{ $recurring->NextRunDate ? \Carbon\Carbon::parse($recurring->NextRunDate)->format('d/m/Y') : 'N/A' }}</strong></div>
                            <div class="small">Frequency: <strong>{{ ucfirst( $frequencies[$recurring->Frequency ?? ''] ?? '-' ) }}</strong></div>
                            <div class="small">End: <strong>{{ $recurring->CutOffDate ? \Carbon\Carbon::parse($recurring->CutOffDate)->format('d/m/Y') : ($journalEntry->Date ? \Carbon\Carbon::parse($journalEntry->Date)->format('d/m/Y') : 'N/A') }}</strong></div>
                        @else
                            <div class="small text-muted"><i class="fas fa-info-circle me-1"></i>No recurring schedule defined.</div>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted fw-semibold">Description:</label>
                        <div class="text-break">{{ $journalEntry->Description }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted fw-semibold">Created By:</label>
                        <div>{{ $journalEntry->createdBy->Name ?? 'System' }}</div>
                    </div>
                </div>

                {{-- Journal Lines --}}
                <h6 class="border-bottom pb-2 text-info">Journal Lines</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover align-middle" style="table-layout:auto; width:100%">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>GL Account</th>
                            <th class="text-start">Debit</th>
                            <th class="text-start">Credit</th>
                            <th class="text-start">Amount</th>
                            <th>Narration</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($journalEntry->journalLines as $index => $line)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $line->glAccount->GLName ?? '-' }}</td>
                                <td class="text-danger text-start">
                                    {{ number_format($line->Debit, 2) }}
                                </td>
                                <td class="text-success text-start">
                                    {{ number_format($line->Credit, 2) }}
                                </td>
                                <td class="text-start">{{ number_format($line->Amount, 2) }}</td>
                                <td>
                                    <details>
                                        <summary class="narration-summary">{{ Str::limit($line->Narration, 50) }}</summary>
                                        <div class="narration-full">{{ $line->Narration }}</div>
                                    </details>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Action Buttons --}}
                @if($journalEntry->ApprovalStatus=='draft')
                    <div class="mt-4 d-flex justify-content-end gap-3">
                        <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#actionRejectModal" data-action="reject">
                            <i class="fas fa-times-circle me-1"></i> Reject
                        </button>
                        <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#actionApproveModal" data-action="approve">
                            <i class="fas fa-check-circle me-1"></i> Approve
                        </button>
                    </div>
                @endif

                <a href="{{ route('recurrentjournal.index') }}" class="no-print">
                    <button class="btn btn-outline-secondary">
                        <i class="fas fa-backward-step me-1"></i> Back
                    </button>
                </a>
            </div>
        </div>
    </div>
    @if($journalEntry->ApprovalStatus=='draft')
        {{-- Approve Modal --}}
        <div class="modal fade" id="actionApproveModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('journalApproval', $journalEntry->Id) }}">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="action_type" value="approve" id="actionType">
                    <input type="hidden" name="journalID" value="{{ $journalEntry->Id}}" id="actionType">
                    <div class="modal-content rounded-4 shadow">
                        <div class="modal-header bg-light border-0">
                            <h5 class="modal-title text-success" id="actionModalLabel">Confirm Approval</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="reason" class="form-label">Reason</label>
                                <textarea class="form-control" name="Reason" id="reason" rows="3" required placeholder="Enter reason here..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-success" id="postBtn" type="submit" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Processing...'; this.form.submit();}">Approve</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Reject Modal --}}
        <div class="modal fade" id="actionRejectModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('journalApproval', $journalEntry->Id) }}">
                    @csrf
                    @method('Post')
                    <input type="hidden" name="action_type" value="reject" id="actionType">
                    <input type="hidden" name="journalID" value="{{ $journalEntry->Id}}" id="actionType">
                    <div class="modal-content rounded-4 shadow">
                        <div class="modal-header bg-light border-0">
                            <h5 class="modal-title text-danger" id="actionModalLabel">Confirm Rejection</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="reason" class="form-label">Reason</label>
                                <textarea class="form-control" name="Reason" id="reason" rows="3" required placeholder="Enter reason here..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-danger" id="postBtn" type="submit" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Processing...'; this.form.submit();}">Reject</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@section('styles')
    <style>
        @media print {
            html, body { margin: 0 !important; padding: 0 !important; }
            .btn, .navbar, .pagination, .no-print { display: none !important; }
            .card { border: none !important; box-shadow: none !important; }
            .container { max-width: none !important; width: 100% !important; }
            .table-responsive { overflow: visible !important; }
        }
    </style>
@endsection
