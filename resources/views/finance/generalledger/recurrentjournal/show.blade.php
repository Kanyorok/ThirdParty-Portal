@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                <h5 class="mb-0 text-info">
                    <i class="fas fa-retweet me-2"></i> Recurrent Journal Details
                    <small class="text-muted">#{{ $journalEntry->RefNo }}</small>
                </h5>
            </div>

            <div class="card-body">
                {{-- Header Info --}}
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <label class="text-muted fw-semibold">Date Details:</label>
                        <div class="small">Start: <strong>{{ \Carbon\Carbon::parse($journalEntry->recurringJournals->StartDate)->format('Y-m-d') }}</strong></div>
                        <div class="small">Next Run: <strong>{{ \Carbon\Carbon::parse($journalEntry->NextRunDate)->format('Y-m-d') }}</strong></div>
                        <div class="small">Frequency: <strong>{{ ucfirst($journalEntry->Frequency ?? '-') }}</strong></div>
                        <div class="small">End: <strong>{{ \Carbon\Carbon::parse($journalEntry->CuttOffDate ?? $journalEntry->Date)->format('Y-m-d') }}</strong></div>
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
                    <table class="table table-bordered table-sm table-hover align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>GL Account</th>
                            <th>Debit</th>
                            <th>Credit</th>
                            <th>Amount</th>
                            <th>Narration</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($journalEntry->journalLines as $index => $line)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $line->glAccount->GLName ?? '-' }}</td>
                                <td class="text-danger">
                                    {{ number_format($line->Debit, 2) }}
                                </td>
                                <td class="text-success">
                                    {{ number_format($line->Credit, 2) }}
                                </td>
                                <td>{{ number_format($line->Amount, 2) }}</td>
                                <td>{{ $line->Narration }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Action Buttons --}}
                <div class="mt-4 d-flex justify-content-end gap-3">
                    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#actionRejectModal">
                        <i class="fas fa-times-circle me-1"></i> Reject
                    </button>
                    <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#actionApproveModal">
                        <i class="fas fa-check-circle me-1"></i> Approve
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Approval Modal --}}
    <div class="modal fade" id="actionApproveModal" tabindex="-1" aria-labelledby="approveLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('journalentry.action', $journalEntry->Id) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="action_type" value="approve">
                <div class="modal-content rounded-4 shadow">
                    <div class="modal-header bg-light border-bottom-0">
                        <h5 class="modal-title text-success"><i class="fas fa-check-circle me-2"></i> Approve Journal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label for="reason_approve" class="form-label">Reason</label>
                        <textarea name="reason" id="reason_approve" rows="3" class="form-control" placeholder="Enter reason..." required></textarea>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Confirm Approve</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Rejection Modal --}}
    <div class="modal fade" id="actionRejectModal" tabindex="-1" aria-labelledby="rejectLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('journalentry.action', $journalEntry->Id) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="action_type" value="reject">
                <div class="modal-content rounded-4 shadow">
                    <div class="modal-header bg-light border-bottom-0">
                        <h5 class="modal-title text-danger"><i class="fas fa-times-circle me-2"></i> Reject Journal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label for="reason_reject" class="form-label">Reason</label>
                        <textarea name="reason" id="reason_reject" rows="3" class="form-control" placeholder="Enter reason..." required></textarea>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Confirm Reject</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
