@extends('layouts.app')

@section('title', 'Transfer Request')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Transfer Request</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.movements.transfers.index') }}">Back</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label text-muted">Employee</label>
                    <div>{{ $transfer->employee->FirstName ?? '' }} {{ $transfer->employee->LastName ?? '' }}</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted">From Branch</label>
                    <div>{{ $transfer->FromBranchID ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted">To Branch</label>
                    <div>{{ $transfer->ToBranchID ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted">From Department</label>
                    <div>{{ $transfer->FromDepartmentID ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted">To Department</label>
                    <div>{{ $transfer->ToDepartmentID ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted">From Role</label>
                    <div>{{ $transfer->FromRoleID ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted">To Role</label>
                    <div>{{ $transfer->ToRoleID ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted">Effective Date</label>
                    <div>{{ optional($transfer->EffectiveDate)->format('Y-m-d') ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted">Status</label>
                    <div>{{ $transfer->Status }}</div>
                </div>
                <div class="col-md-12">
                    <label class="form-label text-muted">Reason</label>
                    <div>{{ $transfer->Reason ?? '-' }}</div>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <form method="POST" action="{{ route('hr.movements.transfers.approve', $transfer->Id) }}">
                    @csrf
                    <input type="hidden" name="ApprovalComment" value="Approved">
                    <button class="btn btn-success" type="submit">Approve</button>
                </form>
                <form method="POST" action="{{ route('hr.movements.transfers.reject', $transfer->Id) }}">
                    @csrf
                    <input type="hidden" name="ApprovalComment" value="Rejected">
                    <button class="btn btn-outline-danger" type="submit">Reject</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
