@extends('layouts.app')

@section('title', 'Acting Assignment')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Acting Assignment</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.movements.acting.index') }}">Back</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label text-muted">Employee</label>
                    <div>{{ $assignment->employee->FirstName ?? '' }} {{ $assignment->employee->LastName ?? '' }}</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted">Acting Branch</label>
                    <div>{{ $assignment->ActingBranchID ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted">Acting Department</label>
                    <div>{{ $assignment->ActingDepartmentID ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted">Acting Role</label>
                    <div>{{ $assignment->ActingRoleID ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted">Start Date</label>
                    <div>{{ optional($assignment->StartDate)->format('Y-m-d') ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted">End Date</label>
                    <div>{{ optional($assignment->EndDate)->format('Y-m-d') ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted">Status</label>
                    <div>{{ $assignment->Status }}</div>
                </div>
                <div class="col-md-12">
                    <label class="form-label text-muted">Reason</label>
                    <div>{{ $assignment->Reason ?? '-' }}</div>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <form method="POST" action="{{ route('hr.movements.acting.approve', $assignment->Id) }}">
                    @csrf
                    <input type="hidden" name="ApprovalComment" value="Approved">
                    <button class="btn btn-success" type="submit">Approve</button>
                </form>
                <form method="POST" action="{{ route('hr.movements.acting.reject', $assignment->Id) }}">
                    @csrf
                    <input type="hidden" name="ApprovalComment" value="Rejected">
                    <button class="btn btn-outline-danger" type="submit">Reject</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
