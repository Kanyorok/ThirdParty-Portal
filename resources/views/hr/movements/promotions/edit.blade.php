@extends('layouts.app')

@section('title', 'Edit Promotion')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Promotion</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.movements.promotions.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.movements.promotions.update', $promotion->Id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Employee</label>
                        <select name="EmployeeID" class="form-select">
                            <option value="">Select</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->Id }}" @selected(old('EmployeeID', $promotion->EmployeeID) == $emp->Id)>{{ $emp->FirstName }} {{ $emp->LastName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">From Grade</label>
                        <select name="FromGradeID" class="form-select">
                            <option value="">Select</option>
                            @foreach($grades as $grade)
                                <option value="{{ $grade->Id }}" @selected(old('FromGradeID', $promotion->FromGradeID) == $grade->Id)>{{ $grade->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">To Grade</label>
                        <select name="ToGradeID" class="form-select">
                            <option value="">Select</option>
                            @foreach($grades as $grade)
                                <option value="{{ $grade->Id }}" @selected(old('ToGradeID', $promotion->ToGradeID) == $grade->Id)>{{ $grade->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">From Role</label>
                        <select name="FromRoleID" class="form-select">
                            <option value="">Select</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->Id }}" @selected(old('FromRoleID', $promotion->FromRoleID) == $role->Id)>{{ $role->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">To Role</label>
                        <select name="ToRoleID" class="form-select">
                            <option value="">Select</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->Id }}" @selected(old('ToRoleID', $promotion->ToRoleID) == $role->Id)>{{ $role->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Effective Date *</label>
                        <input type="date" name="EffectiveDate" class="form-control" value="{{ old('EffectiveDate', optional($promotion->EffectiveDate)->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">From Salary</label>
                        <input type="number" step="0.01" name="FromSalary" class="form-control" value="{{ old('FromSalary', $promotion->FromSalary) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To Salary</label>
                        <input type="number" step="0.01" name="ToSalary" class="form-control" value="{{ old('ToSalary', $promotion->ToSalary) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="Status" class="form-select">
                            @foreach(['Pending','Approved','Rejected'] as $st)
                                <option value="{{ $st }}" @selected(old('Status', $promotion->Status) == $st)>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Reason</label>
                        <textarea name="Reason" class="form-control" rows="2">{{ old('Reason', $promotion->Reason) }}</textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button class="btn btn-primary" type="submit">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
