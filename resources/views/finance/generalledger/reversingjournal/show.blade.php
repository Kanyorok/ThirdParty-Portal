@extends('layouts.app')

@section('content')
    <div class="container mt-0">
        <div class="card shadow-sm rounded-4 border-0">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-danger"><i class="fas fa-undo-alt"></i> Reverse Journal <i class="text-info">#{{ $originalJournalRef }}</i></h5>
                    <span class="text-end">Approval Status:
                        @if($journalEntry->ApprovalStatus == 'posted')
                            <span class="badge bg-success">Approved</span>
                        @elseif($journalEntry->ApprovalStatus == 'rejected')
                            <span class="badge bg-danger">Rejected</span>
                        @elseif($journalEntry->ApprovalStatus == 'draft')
                            <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                    </span>
                </div>
            </div>
            <div class="card-body">
                {{-- Header Info --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="fw-semibold text-muted">Original Journal Ref:</label>
                        <div>{{ $journalEntry->RefNo }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-semibold text-muted">Date:</label>
                        <div>{{ \Carbon\Carbon::parse($journalEntry->Date)->format('Y-m-d') }}</div>
                    </div>
                    <div class="col-md-6 mt-3">
                        <label class="fw-semibold text-muted">Description:</label>
                        <div>{{ $journalEntry->Description }}</div>
                    </div>

                    <div class="col-md-6 mt-3">
                        <label class="fw-semibold text-muted">Created By:</label>
                        <div>{{ $journalEntry->createdBy->Name }}</div>
                    </div>
                </div>

                {{-- Journal Lines --}}
                <h6 class="border-bottom pb-2 text-info">Journal Lines</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle table-bordered">
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
                                <td>{{ $line->glAccount->GLName }}</td>
                                <td class="{{ $line->Debit > 0 ? 'text-danger' : '' }}">
                                    {{ number_format($line->Debit, 2) }}
                                </td>
                                <td class="{{ $line->Credit > 0 ? 'text-success' : '' }}">
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

                <a href="{{ route('reversingjournal.index') }}">
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
