@extends('layouts.app')

@section('title', 'Promotion Request')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Promotion Request</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.movements.promotions.index') }}">Back</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label text-muted">Employee</label>
                    <div>{{ $promotion->employee->FirstName ?? '' }} {{ $promotion->employee->LastName ?? '' }}</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted">From Grade</label>
                    <div>{{ $promotion->FromGradeID ?? '-' }}</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted">To Grade</label>
                    <div>{{ $promotion->ToGradeID ?? '-' }}</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted">From Role</label>
                    <div>{{ $promotion->FromRoleID ?? '-' }}</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted">To Role</label>
                    <div>{{ $promotion->ToRoleID ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted">Effective Date</label>
                    <div>{{ optional($promotion->EffectiveDate)->format('Y-m-d') ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted">From Salary</label>
                    <div>{{ $promotion->FromSalary ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted">To Salary</label>
                    <div>{{ $promotion->ToSalary ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted">Status</label>
                    <div>{{ $promotion->Status }}</div>
                </div>
                <div class="col-md-12">
                    <label class="form-label text-muted">Reason</label>
                    <div>{{ $promotion->Reason ?? '-' }}</div>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <form method="POST" action="{{ route('hr.movements.promotions.approve', $promotion->Id) }}">
                    @csrf
                    <input type="hidden" name="ApprovalComment" value="Approved">
                    <button class="btn btn-success" type="submit">Approve</button>
                </form>
                <form method="POST" action="{{ route('hr.movements.promotions.reject', $promotion->Id) }}">
                    @csrf
                    <input type="hidden" name="ApprovalComment" value="Rejected">
                    <button class="btn btn-outline-danger" type="submit">Reject</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
