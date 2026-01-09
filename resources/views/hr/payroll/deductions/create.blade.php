@extends('layouts.app')

@section('title', 'New Deduction')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">New Deduction</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.payroll.deductions.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.payroll.deductions.store') }}">
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
                        <label class="form-label">Deduction *</label>
                        <select name="DeductionID" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($deductions as $ded)
                                <option value="{{ $ded->Id }}" @selected(old('DeductionID')==$ded->Id)>{{ $ded->Name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Mandatory deductions are not listed here; use “Sync Mandatory” to assign them. Staff loans are managed under Payroll → Staff Loans.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Amount (override)</label>
                        <input type="number" step="0.01" name="Amount" id="Amount" class="form-control" value="{{ old('Amount') }}" placeholder="Calculated from configured rules" disabled>
                        <div class="form-text">
                            Most deductions (e.g., PAYE, SHIF, NSSF) are calculated from configured rules during payroll generation. Use override only for special cases.
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Month *</label>
                        <input type="number" name="Month" class="form-control" value="{{ old('Month', now()->month) }}" min="1" max="12" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Year *</label>
                        <input type="number" name="Year" class="form-control" value="{{ old('Year', now()->year) }}" required>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="IsRecurring" value="1" id="recurring" @checked(old('IsRecurring', false))>
                                <label class="form-check-label" for="recurring">Recurring</label>
                            </div>
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="OverrideAmount" value="1" id="overrideAmount" @checked(old('OverrideAmount', false))>
                                <label class="form-check-label" for="overrideAmount">Override amount for this month</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        const checkbox = document.getElementById('overrideAmount');
        const amount = document.getElementById('Amount');
        if (!checkbox || !amount) return;

        function sync() {
            const enabled = !!checkbox.checked;
            amount.disabled = !enabled;
            if (!enabled) {
                amount.value = '';
            }
        }

        checkbox.addEventListener('change', sync);
        sync();
    })();
</script>
@endsection
