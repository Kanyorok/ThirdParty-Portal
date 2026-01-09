@extends('layouts.app')

@section('title', 'Decision')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Decision</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.discipline.cases.show', $case->Id) }}">Back</a>
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
            <form action="{{ route('hr.discipline.cases.decision.store', $case->Id) }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Decision Date *</label>
                        <input type="date" name="DecisionDate" class="form-control" value="{{ old('DecisionDate', optional($decision->DecisionDate)->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Legal Reference</label>
                        <select name="LegalRefID" class="form-select">
                            <option value="">Select</option>
                            @foreach($legalRefs as $ref)
                                <option value="{{ $ref->Id }}" @selected(old('LegalRefID', $decision->LegalRefID ?? null) == $ref->Id)>
                                    {{ $ref->Section }} - {{ $ref->Title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sanction *</label>
                        <select name="SanctionID" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($sanctions as $sanction)
                                <option value="{{ $sanction->Id }}" @selected(old('SanctionID', $decision->SanctionID ?? null) == $sanction->Id)>
                                    {{ $sanction->Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sanction Start</label>
                        <input type="date" name="SanctionStartDate" class="form-control" value="{{ old('SanctionStartDate', optional($decision->SanctionStartDate)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sanction End</label>
                        <input type="date" name="SanctionEndDate" class="form-control" value="{{ old('SanctionEndDate', optional($decision->SanctionEndDate)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Policy Clause</label>
                        <input type="text" name="PolicyClause" class="form-control" value="{{ old('PolicyClause', $decision->PolicyClause ?? '') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Decision Summary *</label>
                        <textarea name="DecisionSummary" class="form-control" rows="4" required>{{ old('DecisionSummary', $decision->DecisionSummary ?? '') }}</textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <input type="text" name="Status" class="form-control" value="{{ old('Status', $decision->Status ?? 'Approved') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="PayrollImpact" value="1" @checked(old('PayrollImpact', $decision->PayrollImpact ?? false))>
                            <label class="form-check-label">Payroll Impact</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payroll Impact Amount</label>
                        <input type="number" step="0.01" name="PayrollImpactAmount" class="form-control" value="{{ old('PayrollImpactAmount') }}">
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('hr.discipline.cases.show', $case->Id) }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Decision</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
