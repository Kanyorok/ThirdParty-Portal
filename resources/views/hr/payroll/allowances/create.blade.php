@extends('layouts.app')

@section('title', 'New Allowance')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">New Allowance</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.payroll.allowances.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.payroll.allowances.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Employee *</label>
                        <select name="EmployeeID" id="EmployeeID" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->Id }}" data-grade="{{ $emp->GradeID }}" @selected(old('EmployeeID') == $emp->Id)>{{ $emp->FirstName }} {{ $emp->LastName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Allowance *</label>
                        <select name="AllowanceID" id="AllowanceID" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($allowances as $a)
                                @php $gradeIds = $a->grades->pluck('Id')->toArray(); @endphp
                                <option value="{{ $a->Id }}"
                                        data-taxable="{{ $a->IsTaxable ? 1 : 0 }}"
                                        data-name="{{ $a->Name }}"
                                        data-grades="{{ implode(',', $gradeIds) }}"
                                    @selected(old('AllowanceID')==$a->Id)>{{ $a->Name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Mandatory allowances are auto-applied during payroll and are not listed here.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Amount *</label>
                        <input type="number" step="0.01" name="Amount" class="form-control" value="{{ old('Amount') }}" required>
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
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="IsTaxable" value="1" id="taxable" @checked(old('IsTaxable', true))>
                            <label class="form-check-label" for="taxable">Taxable</label>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="IsRecurring" value="1" id="recurring" @checked(old('IsRecurring', false))>
                            <label class="form-check-label" for="recurring">Recurring</label>
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
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const allowanceSelect = document.getElementById('AllowanceID');
    const taxable = document.getElementById('taxable');
    const empSelect = document.getElementById('EmployeeID');
    allowanceSelect?.addEventListener('change', function(){
        const opt = allowanceSelect.options[allowanceSelect.selectedIndex];
        const isTaxable = opt ? opt.getAttribute('data-taxable') : '1';
        if (taxable) taxable.checked = isTaxable === '1';
    });

    const filterAllowances = () => {
        if (!empSelect || !allowanceSelect) return;
        const grade = empSelect.options[empSelect.selectedIndex]?.getAttribute('data-grade') || '';
        Array.from(allowanceSelect.options).forEach(opt => {
            if (!opt.value) return;
            const grades = (opt.getAttribute('data-grades') || '').split(',').filter(Boolean);
            const allowed = grades.length === 0 || grades.includes(grade);
            opt.disabled = !allowed;
            if (!allowed && opt.selected) {
                opt.selected = false;
            }
        });
    };
    empSelect?.addEventListener('change', filterAllowances);
    filterAllowances();
});
</script>
@endpush
@endsection
