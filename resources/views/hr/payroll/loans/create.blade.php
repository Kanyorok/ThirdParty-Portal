@extends('layouts.app')

@section('title', 'New Staff Loan')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">New Staff Loan</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.payroll.loans.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.payroll.loans.store') }}" id="loanForm">
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
                        <label class="form-label">Loan Ref (CBS)</label>
                        <input type="text" name="LoanRef" class="form-control" value="{{ old('LoanRef') }}" placeholder="Optional reference from CBS">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Loan Name *</label>
                        <input type="text" name="Name" class="form-control" value="{{ old('Name', 'Staff Loan') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Principal *</label>
                        <input type="number" step="0.01" name="Principal" id="principal" class="form-control" value="{{ old('Principal') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Interest Rate (% p.a)</label>
                        <input type="number" step="0.0001" name="InterestRate" id="interestRate" class="form-control" value="{{ old('InterestRate', 0) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tenure (Months) *</label>
                        <input type="number" name="TenureMonths" id="tenure" class="form-control" value="{{ old('TenureMonths', 12) }}" min="1" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Start Date *</label>
                        <input type="date" name="StartDate" class="form-control" value="{{ old('StartDate', now()->startOfMonth()->toDateString()) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Monthly Repayment (Auto-calculated)</label>
                        <input type="text" id="monthlyRepaymentDisplay" class="form-control bg-light" readonly placeholder="Will be calculated">
                        <input type="hidden" name="InstallmentAmount" id="installmentAmount" value="{{ old('InstallmentAmount') }}">
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
    const principalInput = document.getElementById('principal');
    const interestRateInput = document.getElementById('interestRate');
    const tenureInput = document.getElementById('tenure');
    const monthlyRepaymentDisplay = document.getElementById('monthlyRepaymentDisplay');
    const installmentAmountHidden = document.getElementById('installmentAmount');
    
    function calculateMonthlyRepayment() {
        const principal = parseFloat(principalInput.value) || 0;
        const annualRate = parseFloat(interestRateInput.value) || 0;
        const tenure = parseInt(tenureInput.value) || 1;
        
        if (principal <= 0 || tenure <= 0) {
            monthlyRepaymentDisplay.value = '';
            installmentAmountHidden.value = '';
            return;
        }
        
        let monthlyRepayment;
        
        if (annualRate === 0) {
            // No interest - simple division
            monthlyRepayment = principal / tenure;
        } else {
            // Calculate with interest using reducing balance method
            const monthlyRate = annualRate / 100 / 12;
            
            // PMT formula: P * (r * (1 + r)^n) / ((1 + r)^n - 1)
            const numerator = monthlyRate * Math.pow(1 + monthlyRate, tenure);
            const denominator = Math.pow(1 + monthlyRate, tenure) - 1;
            monthlyRepayment = principal * (numerator / denominator);
        }
        
        // Round to 2 decimal places
        monthlyRepayment = Math.round(monthlyRepayment * 100) / 100;
        
        monthlyRepaymentDisplay.value = monthlyRepayment.toFixed(2);
        installmentAmountHidden.value = monthlyRepayment.toFixed(2);
    }
    
    // Calculate on input changes
    principalInput.addEventListener('input', calculateMonthlyRepayment);
    interestRateInput.addEventListener('input', calculateMonthlyRepayment);
    tenureInput.addEventListener('input', calculateMonthlyRepayment);
    
    // Calculate on page load if values exist
    calculateMonthlyRepayment();
});
</script>
@endpush
@endsection
