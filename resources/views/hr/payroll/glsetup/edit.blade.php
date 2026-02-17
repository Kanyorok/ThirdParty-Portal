@extends('layouts.app')

@section('title', 'Payroll GL Setup')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Payroll GL Setup</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.statutory.deductions.index') }}">Back</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

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
            <form method="POST" action="{{ route('hr.payroll.glsetup.update') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Payroll Control / Salaries Payable GL *</label>
                        <select class="form-select" name="PayrollControlGLAccountID" required>
                            <option value="">Select</option>
                            @foreach($glAccounts as $gl)
                                <option value="{{ $gl->Id }}" @selected(old('PayrollControlGLAccountID', $settings?->PayrollControlGLAccountID) == $gl->Id)>
                                    {{ $gl->GLCode }} - {{ $gl->GLName }} @if($gl->CBSAccountCode) (CBS: {{ $gl->CBSAccountCode }}) @endif
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">This is the clearing account used in payroll posting (CR for earnings, DR for deductions/payments).</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Basic Salary Expense GL *</label>
                        <select class="form-select" name="BasicSalaryExpenseGLAccountID" required>
                            <option value="">Select</option>
                            @foreach($glAccounts as $gl)
                                <option value="{{ $gl->Id }}" @selected(old('BasicSalaryExpenseGLAccountID', $settings?->BasicSalaryExpenseGLAccountID) == $gl->Id)>
                                    {{ $gl->GLCode }} - {{ $gl->GLName }} @if($gl->CBSAccountCode) (CBS: {{ $gl->CBSAccountCode }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Salary Payment Bank GL (optional)</label>
                        <select class="form-select" name="SalaryBankGLAccountID">
                            <option value="">Select</option>
                            @foreach($glAccounts as $gl)
                                <option value="{{ $gl->Id }}" @selected(old('SalaryBankGLAccountID', $settings?->SalaryBankGLAccountID) == $gl->Id)>
                                    {{ $gl->GLCode }} - {{ $gl->GLName }} @if($gl->CBSAccountCode) (CBS: {{ $gl->CBSAccountCode }}) @endif
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Used later for salary settlement posting (DR Payroll Control / CR Bank).</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">CBS Settlement GL (optional)</label>
                        <select class="form-select" name="SalaryCBSSettlementGLAccountID">
                            <option value="">Select</option>
                            @foreach($glAccounts as $gl)
                                <option value="{{ $gl->Id }}" @selected(old('SalaryCBSSettlementGLAccountID', $settings?->SalaryCBSSettlementGLAccountID) == $gl->Id)>
                                    {{ $gl->GLCode }} - {{ $gl->GLName }} @if($gl->CBSAccountCode) (CBS: {{ $gl->CBSAccountCode }}) @endif
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">If paying staff via CBS integration, we'll post/emit using this settlement GL.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Gratuity Expense GL (optional)</label>
                        <select class="form-select" name="GratuityExpenseGLAccountID">
                            <option value="">Select</option>
                            @foreach($glAccounts as $gl)
                                <option value="{{ $gl->Id }}" @selected(old('GratuityExpenseGLAccountID', $settings?->GratuityExpenseGLAccountID) == $gl->Id)>
                                    {{ $gl->GLCode }} - {{ $gl->GLName }} @if($gl->CBSAccountCode) (CBS: {{ $gl->CBSAccountCode }}) @endif
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Used for gratuity accrual expense.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Gratuity Liability GL (optional)</label>
                        <select class="form-select" name="GratuityLiabilityGLAccountID">
                            <option value="">Select</option>
                            @foreach($glAccounts as $gl)
                                <option value="{{ $gl->Id }}" @selected(old('GratuityLiabilityGLAccountID', $settings?->GratuityLiabilityGLAccountID) == $gl->Id)>
                                    {{ $gl->GLCode }} - {{ $gl->GLName }} @if($gl->CBSAccountCode) (CBS: {{ $gl->CBSAccountCode }}) @endif
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Used to record gratuity payable liability.</div>
                    </div>


                    <div class="col-md-4">
                        <label class="form-label">Currency (optional)</label>
                        <select class="form-select" name="CurrencyID">
                            <option value="">Select</option>
                            @foreach(($currencies ?? []) as $currency)
                                <option value="{{ $currency->Id }}" @selected(old('CurrencyID', $settings?->CurrencyID) == $currency->Id)>
                                    {{ $currency->Code }} - {{ $currency->Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit">Save Setup</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


