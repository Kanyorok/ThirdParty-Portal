@extends('layouts.app')

@section('title', 'Edit Training Program')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Training Program</h2>
        <a class="btn btn-outline-secondary" href="{{ route('crm.training.programs.index') }}">Back</a>
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

    <div class="alert alert-info d-flex justify-content-between align-items-center">
        <span>This program has <strong>{{ $targetClientCount }}</strong> participant(s).</span>
        <a class="btn btn-sm btn-primary" href="{{ route('crm.training.programs.participants', $program->Id) }}">Manage Participants</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('crm.training.programs.update', $program->Id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Code *</label>
                        <input type="text" name="Code" class="form-control" value="{{ old('Code', $program->Code) }}" required>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">Title *</label>
                        <input type="text" name="Title" class="form-control" value="{{ old('Title', $program->Title) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <select name="CategoryID" class="form-select">
                            <option value="">Select</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->Id }}" @selected(old('CategoryID', $program->CategoryID) == $category->Id)>{{ $category->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Delivery Mode</label>
                        <select name="DeliveryMode" class="form-select">
                            <option value="">Select</option>
                            @foreach($deliveryModes as $mode)
                                <option value="{{ $mode }}" @selected(old('DeliveryMode', $program->DeliveryMode) === $mode)>{{ $mode }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Duration (hours)</label>
                        <input type="number" step="0.01" name="DurationHours" class="form-control" value="{{ old('DurationHours', $program->DurationHours) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Objectives</label>
                        <textarea name="Objectives" class="form-control" rows="3">{{ old('Objectives', $program->Objectives) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Target Audience (notes)</label>
                        <textarea name="TargetAudience" class="form-control" rows="3">{{ old('TargetAudience', $program->TargetAudience) }}</textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Budgeted Cost</label>
                        <input type="number" step="0.01" name="BudgetedCost" class="form-control" value="{{ old('BudgetedCost', $program->BudgetedCost) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Actual Cost</label>
                        <input type="number" step="0.01" name="ActualCost" class="form-control" value="{{ old('ActualCost', $program->ActualCost) }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="IsMandatory" value="1" id="IsMandatory" @checked(old('IsMandatory', $program->IsMandatory))>
                            <label class="form-check-label" for="IsMandatory">Mandatory</label>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="HasCertification" value="1" id="HasCertification" @checked(old('HasCertification', $program->HasCertification))>
                            <label class="form-check-label" for="HasCertification">Certification</label>
                        </div>
                    </div>
                    <div class="col-md-3 js-certification-settings">
                        <label class="form-label">Certification Scope</label>
                        <select name="CertificateScope" id="CertificateScope" class="form-select">
                            <option value="Session" @selected(old('CertificateScope', $program->CertificateScope ?? 'Session') === 'Session')>Per Session</option>
                            <option value="Program" @selected(old('CertificateScope', $program->CertificateScope) === 'Program')>Whole Program/Course</option>
                        </select>
                    </div>
                    <div class="col-md-3 js-program-completion-settings">
                        <label class="form-label">Completion Rule</label>
                        <select name="CertificationCompletionRule" id="CertificationCompletionRule" class="form-select">
                            <option value="AnySession" @selected(old('CertificationCompletionRule', $program->CertificationCompletionRule ?? 'AnySession') === 'AnySession')>Any Attended Session</option>
                            <option value="MinSessions" @selected(old('CertificationCompletionRule', $program->CertificationCompletionRule) === 'MinSessions')>Minimum Sessions</option>
                            <option value="AllSessions" @selected(old('CertificationCompletionRule', $program->CertificationCompletionRule) === 'AllSessions')>All Program Sessions</option>
                        </select>
                    </div>
                    <div class="col-md-3 js-program-completion-settings js-min-sessions-wrap">
                        <label class="form-label">Minimum Sessions</label>
                        <input type="number" min="1" step="1" name="CertificationMinimumSessions" id="CertificationMinimumSessions" class="form-control" value="{{ old('CertificationMinimumSessions', $program->CertificationMinimumSessions ?: 1) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="Status" class="form-select">
                            <option value="Active" @selected(old('Status', $program->Status) === 'Active')>Active</option>
                            <option value="Inactive" @selected(old('Status', $program->Status) === 'Inactive')>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a class="btn btn-outline-primary" href="{{ route('crm.training.programs.participants', $program->Id) }}">Manage Participants</a>
                    <button class="btn btn-primary" type="submit">Update Program</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const hasCertification = document.getElementById('HasCertification');
        const certScope = document.getElementById('CertificateScope');
        const completionRule = document.getElementById('CertificationCompletionRule');
        const certificationSettings = document.querySelectorAll('.js-certification-settings');
        const programSettings = document.querySelectorAll('.js-program-completion-settings');
        const minSessionsWrap = document.querySelector('.js-min-sessions-wrap');
        const minSessionsInput = document.getElementById('CertificationMinimumSessions');

        const syncCertificationOptions = function () {
            const certificationEnabled = hasCertification && hasCertification.checked;
            const programScopeSelected = certificationEnabled && certScope && certScope.value === 'Program';
            const minSessionsSelected = programScopeSelected && completionRule && completionRule.value === 'MinSessions';

            certificationSettings.forEach(function (el) {
                el.classList.toggle('d-none', !certificationEnabled);
            });
            programSettings.forEach(function (el) {
                el.classList.toggle('d-none', !programScopeSelected);
            });

            if (minSessionsWrap) {
                minSessionsWrap.classList.toggle('d-none', !minSessionsSelected);
            }

            if (minSessionsInput) {
                if (!minSessionsSelected) {
                    minSessionsInput.value = '';
                } else if (!minSessionsInput.value) {
                    minSessionsInput.value = '1';
                }
            }
        };

        hasCertification?.addEventListener('change', syncCertificationOptions);
        certScope?.addEventListener('change', syncCertificationOptions);
        completionRule?.addEventListener('change', syncCertificationOptions);

        syncCertificationOptions();
    });
</script>
@endpush
