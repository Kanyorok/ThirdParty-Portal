@extends('layouts.app')

@section('title', 'Edit Deduction')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Deduction</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.statutory.deductions.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.statutory.deductions.update', $deduction->Id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Code *</label>
                        <input type="hidden" name="Code" value="{{ $deduction->Code }}">
                        <input type="text" class="form-control" value="{{ $deduction->Code }}" readonly>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Name *</label>
                        <input type="text" name="Name" class="form-control" value="{{ old('Name', $deduction->Name) }}" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <input type="text" name="Description" class="form-control" value="{{ old('Description', $deduction->Description) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Debit GL (ERP)</label>
                        <select name="DebitGLAccountID" class="form-select">
                            <option value="">Select</option>
                            @foreach(($glAccounts ?? []) as $gl)
                                <option value="{{ $gl->Id }}" @selected(old('DebitGLAccountID', $deduction->DebitGLAccountID) == $gl->Id)>
                                    {{ $gl->GLCode }} - {{ $gl->GLName }} @if($gl->CBSAccountCode) (CBS: {{ $gl->CBSAccountCode }}) @endif
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">CBS GL is picked from the selected ERP GL account.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Credit GL (ERP)</label>
                        <select name="CreditGLAccountID" class="form-select">
                            <option value="">Select</option>
                            @foreach(($glAccounts ?? []) as $gl)
                                <option value="{{ $gl->Id }}" @selected(old('CreditGLAccountID', $deduction->CreditGLAccountID) == $gl->Id)>
                                    {{ $gl->GLCode }} - {{ $gl->GLName }} @if($gl->CBSAccountCode) (CBS: {{ $gl->CBSAccountCode }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <hr>
                        <div class="fw-semibold">Employer Contribution (optional)</div>
                        <div class="text-muted small">Configure any employer match, e.g. SHA at 100% of employee deduction.</div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" name="EmployerContributionEnabled" value="1" id="EmployerContributionEnabled" @checked(old('EmployerContributionEnabled', $deduction->EmployerContributionEnabled))>
                            <label for="EmployerContributionEnabled" class="form-check-label">Employer contributes</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Employer Calc Method</label>
                        <select name="EmployerCalcMethod" class="form-select">
                            <option value="">Select</option>
                            <option value="MatchEmployeeDeduction" @selected(old('EmployerCalcMethod', $deduction->EmployerCalcMethod) === 'MatchEmployeeDeduction')>Match Employee Deduction (%)</option>
                            <option value="PercentageOfBasic" @selected(old('EmployerCalcMethod', $deduction->EmployerCalcMethod) === 'PercentageOfBasic')>Percentage of Basic</option>
                            <option value="PercentageOfGross" @selected(old('EmployerCalcMethod', $deduction->EmployerCalcMethod) === 'PercentageOfGross')>Percentage of Gross</option>
                            <option value="Flat" @selected(old('EmployerCalcMethod', $deduction->EmployerCalcMethod) === 'Flat')>Fixed Amount</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Employer Rate (%)</label>
                        <input type="number" step="0.0001" name="EmployerRate" class="form-control" value="{{ old('EmployerRate', $deduction->EmployerRate) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Employer Amount</label>
                        <input type="number" step="0.01" name="EmployerAmount" class="form-control" value="{{ old('EmployerAmount', $deduction->EmployerAmount) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Employer Debit GL (ERP)</label>
                        <select name="EmployerDebitGLAccountID" class="form-select">
                            <option value="">Select</option>
                            @foreach(($glAccounts ?? []) as $gl)
                                <option value="{{ $gl->Id }}" @selected(old('EmployerDebitGLAccountID', $deduction->EmployerDebitGLAccountID) == $gl->Id)>
                                    {{ $gl->GLCode }} - {{ $gl->GLName }} @if($gl->CBSAccountCode) (CBS: {{ $gl->CBSAccountCode }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Employer Credit GL (ERP)</label>
                        <select name="EmployerCreditGLAccountID" class="form-select">
                            <option value="">Select</option>
                            @foreach(($glAccounts ?? []) as $gl)
                                <option value="{{ $gl->Id }}" @selected(old('EmployerCreditGLAccountID', $deduction->EmployerCreditGLAccountID) == $gl->Id)>
                                    {{ $gl->GLCode }} - {{ $gl->GLName }} @if($gl->CBSAccountCode) (CBS: {{ $gl->CBSAccountCode }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" name="IsMandatory" value="1" id="IsMandatory" @checked(old('IsMandatory', $deduction->IsMandatory))>
                            <label for="IsMandatory" class="form-check-label">Mandatory for all staff</label>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" name="ShowInPayslip" value="1" id="ShowInPayslip" @checked(old('ShowInPayslip', $deduction->ShowInPayslip))>
                            <label for="ShowInPayslip" class="form-check-label">Show in Payslip</label>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" name="IsTaxAllowable" value="1" id="IsTaxAllowable" @checked(old('IsTaxAllowable', $deduction->IsTaxAllowable))>
                            <label for="IsTaxAllowable" class="form-check-label">Tax Allowable (Reduces Taxable Income)</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Applicable For</label>
                        <select name="ApplyFor" class="form-select">
                            <option value="">All</option>
                            @foreach(['Regular','Contract','Intern'] as $type)
                                <option value="{{ $type }}" @selected(old('ApplyFor', $deduction->ApplyFor)==$type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" name="IsActive" value="1" id="IsActive" @checked(old('IsActive', $deduction->IsActive))>
                            <label for="IsActive" class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Update Deduction</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
