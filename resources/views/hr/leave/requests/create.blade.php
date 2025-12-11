<div>
    <!-- Let all your things have their places; let each part of your business have its time. - Benjamin Franklin -->
</div>
@extends('layouts.app')

@section('title', 'New Leave Request')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">New Leave Request</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.leave.requests.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.leave.requests.store') }}">
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
                    <div class="col-md-4">
                        <label class="form-label">Leave Type *</label>
                        <select name="LeaveTypeID" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($types as $type)
                                <option value="{{ $type->Id }}" @selected(old('LeaveTypeID') == $type->Id)>{{ $type->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Start Date *</label>
                        <input type="date" name="StartDate" id="StartDate" class="form-control" value="{{ old('StartDate') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">End Date *</label>
                        <input type="date" name="EndDate" id="EndDate" class="form-control" value="{{ old('EndDate') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Total Days *</label>
                        <input type="number" step="0.25" name="TotalDays" id="TotalDays" class="form-control" value="{{ old('TotalDays') }}" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Reason</label>
                        <textarea name="Reason" class="form-control" rows="2">{{ old('Reason') }}</textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button class="btn btn-primary" type="submit">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const start = document.getElementById('StartDate');
    const end = document.getElementById('EndDate');
    const total = document.getElementById('TotalDays');
    const calc = () => {
        if (!start.value || !end.value) return;
        const s = new Date(start.value);
        const e = new Date(end.value);
        if (isNaN(s) || isNaN(e)) return;
        const diff = (e - s) / (1000*60*60*24);
        if (diff >= 0) total.value = (diff + 1).toFixed(2);
    };
    start.addEventListener('change', calc);
    end.addEventListener('change', calc);
});
</script>
@endpush
@endsection
