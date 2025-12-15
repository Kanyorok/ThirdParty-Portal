@extends('layouts.app')

@section('title', 'New Salary Adjustment')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">New Salary Adjustment</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.payroll.adjustments.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.payroll.adjustments.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Employee *</label>
                        <select name="EmployeeID" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->Id }}" @selected(old('EmployeeID') == $emp->Id)>{{ $emp->FirstName }} {{ $emp->LastName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Type *</label>
                        <select name="Type" class="form-select" required>
                            <option value="">Select</option>
                            <option value="Increment" @selected(old('Type')=='Increment')>Increment</option>
                            <option value="Decrement" @selected(old('Type')=='Decrement')>Decrement</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Amount *</label>
                        <input type="number" step="0.01" name="Amount" class="form-control" value="{{ old('Amount') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Effective Date *</label>
                        <input type="date" name="EffectiveDate" class="form-control" value="{{ old('EffectiveDate') }}" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Reason</label>
                        <textarea name="Reason" class="form-control" rows="2">{{ old('Reason') }}</textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
