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
                        <select name="EmployeeID" id="EmployeeID" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->Id }}"
                                        data-department="{{ $emp->DepartmentID }}"
                                        @selected(old('EmployeeID') == $emp->Id)>{{ $emp->FirstName }} {{ $emp->LastName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Leave Type *</label>
                        <select name="LeaveTypeID" class="form-select" required data-old="{{ old('LeaveTypeID', '') }}">
                            <option value="">Select</option>
                            @foreach($types as $type)
                                <option value="{{ $type->Id }}" @selected(old('LeaveTypeID') == $type->Id)>{{ $type->Name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Only types allowed for your grade/gender are shown.</div>
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
                        <div class="form-text" id="DaysHelp">Auto-calculated from working days, holidays, and half-day rules.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Reliever</label>
                        <select name="RelieverID" id="RelieverID" class="form-select">
                            <option value="">Select</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->Id }}"
                                        data-department="{{ $emp->DepartmentID }}"
                                    @selected(old('RelieverID') == $emp->Id)>{{ $emp->FirstName }} {{ $emp->LastName }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Only employees from the same department are available.</div>
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
    const empSelect = document.getElementById('EmployeeID');
    const relSelect = document.getElementById('RelieverID');
    const daysHelp = document.getElementById('DaysHelp');
    const leaveTypeSelect = document.querySelector('select[name="LeaveTypeID"]');
    const initialLeaveType = leaveTypeSelect?.dataset.old || '';
    const calcUrl = @json(route('hr.leave.requests.calc'));
    const eligibleUrl = @json(route('hr.leave.requests.eligible_types'));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const calc = async () => {
        if (!start.value || !end.value) return;
        if (empSelect && !empSelect.value) {
            daysHelp.textContent = 'Select an employee to calculate days.';
            return;
        }
        daysHelp.textContent = 'Calculating...';
        try {
            const res = await fetch(calcUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({
                    StartDate: start.value,
                    EndDate: end.value,
                    TotalDays: total.value,
                    EmployeeID: empSelect?.value || null
                })
            });
            if (!res.ok) {
                const err = await res.json().catch(() => ({}));
                const msg = err?.errors ? Object.values(err.errors).flat().join(' ') : 'Unable to calculate days.';
                daysHelp.textContent = msg;
                return;
            }
            const data = await res.json();
            if (data?.days !== undefined) {
                total.value = Number(data.days).toFixed(2);
                daysHelp.textContent = 'Auto-calculated from working days, holidays, and half-day rules.';
            }
        } catch (e) {
            daysHelp.textContent = 'Unable to calculate days.';
        }
    };

    const refreshLeaveTypes = async () => {
        if (!leaveTypeSelect) return;
        const empId = empSelect?.value;
        const previous = leaveTypeSelect.value || initialLeaveType;
        const query = empId ? `?employee_id=${empId}` : '';
        leaveTypeSelect.innerHTML = '<option value="">Loading...</option>';
        try {
            const response = await fetch(eligibleUrl + query, {
                headers: {
                    'X-CSRF-TOKEN': csrf,
                },
            });
            if (!response.ok) {
                throw new Error('Unable to load leave types.');
            }
            const types = await response.json();
            leaveTypeSelect.innerHTML = '<option value="">Select</option>';
            types.forEach(type => {
                const option = document.createElement('option');
                option.value = type.Id;
                option.textContent = type.Name;
                if (previous && parseInt(previous) === type.Id) {
                    option.selected = true;
                }
                leaveTypeSelect.appendChild(option);
            });
        } catch (error) {
            leaveTypeSelect.innerHTML = '<option value="">Unable to load leave types</option>';
        }
    };

    const filterRelievers = () => {
        if (!empSelect || !relSelect) return;
        const selEmp = empSelect.options[empSelect.selectedIndex];
        const dept = selEmp ? selEmp.getAttribute('data-department') : null;
        let cleared = false;
        const selectedEmployeeId = empSelect.value;
        Array.from(relSelect.options).forEach(opt => {
            if (!opt.value) return;
            const relDept = opt.getAttribute('data-department');
            const match = !dept || !relDept || dept === relDept;
            const sameEmployee = selectedEmployeeId && opt.value === selectedEmployeeId;
            opt.disabled = !match || sameEmployee;
            opt.hidden = !match || sameEmployee;
            if ((!match || sameEmployee) && opt.selected) {
                opt.selected = false;
                cleared = true;
            }
        });
        if (cleared) {
            relSelect.value = '';
        }
    };

    start.addEventListener('change', calc);
    end.addEventListener('change', calc);
    if (empSelect) {
        empSelect.addEventListener('change', () => {
            refreshLeaveTypes();
            filterRelievers();
            calc();
        });
        filterRelievers();
    }
    refreshLeaveTypes();
    calc();
});
</script>
@endpush
@endsection
