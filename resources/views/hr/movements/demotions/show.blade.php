@extends('layouts.app')

@section('title', 'Demotion Request')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Demotion Request</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.movements.demotions.index') }}">Back</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label text-muted">Employee</label>
                    <div>{{ $demotion->employee->FirstName ?? '' }} {{ $demotion->employee->LastName ?? '' }}</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted">From Grade</label>
                    <div>{{ $demotion->FromGradeID ?? '-' }}</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted">To Grade</label>
                    <div>{{ $demotion->ToGradeID ?? '-' }}</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted">From Role</label>
                    <div>{{ $demotion->FromRoleID ?? '-' }}</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted">To Role</label>
                    <div>{{ $demotion->ToRoleID ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted">Effective Date</label>
                    <div>{{ optional($demotion->EffectiveDate)->format('Y-m-d') ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted">From Salary</label>
                    <div>{{ $demotion->FromSalary ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted">To Salary</label>
                    <div>{{ $demotion->ToSalary ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted">Status</label>
                    <div>{{ $demotion->Status }}</div>
                </div>
                <div class="col-md-12">
                    <label class="form-label text-muted">Reason</label>
                    <div>{{ $demotion->Reason ?? '-' }}</div>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <form method="POST" action="{{ route('hr.movements.demotions.approve', $demotion->Id) }}">
                    @csrf
                    <input type="hidden" name="ApprovalComment" value="Approved">
                    <button class="btn btn-success" type="submit">Approve</button>
                </form>
                <form method="POST" action="{{ route('hr.movements.demotions.reject', $demotion->Id) }}">
                    @csrf
                    <input type="hidden" name="ApprovalComment" value="Rejected">
                    <button class="btn btn-outline-danger" type="submit">Reject</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
