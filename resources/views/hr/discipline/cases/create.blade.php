@extends('layouts.app')

@section('title', 'New Case')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Create Disciplinary Case</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.discipline.cases.index') }}">Back</a>
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
            <form action="{{ route('hr.discipline.cases.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Employee *</label>
                        <select name="EmployeeID" class="form-select" id="caseEmployeeSelect" required>
                            <option value="">Select</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->Id }}" @selected(old('EmployeeID') == $employee->Id)>
                                    {{ $employee->FirstName }} {{ $employee->LastName }} ({{ $employee->EmployeeNo }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Complainant Type</label>
                        <input type="text" name="ComplainantType" class="form-control" value="{{ old('ComplainantType') }}" placeholder="Manager, HR, External">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Complainant (Employee)</label>
                        <select name="ComplainantID" class="form-select" id="caseComplainantSelect">
                            <option value="">Select</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->Id }}" @selected(old('ComplainantID') == $employee->Id)>
                                    {{ $employee->FirstName }} {{ $employee->LastName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Complainant Name (external)</label>
                        <input type="text" name="ComplainantName" class="form-control" value="{{ old('ComplainantName') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Offence *</label>
                        <select name="OffenceID" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($offences as $offence)
                                <option value="{{ $offence->Id }}" @selected(old('OffenceID') == $offence->Id)>
                                    {{ $offence->Name }} ({{ $offence->Severity }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Policy</label>
                        <select name="PolicyID" class="form-select">
                            <option value="">Select</option>
                            @foreach($policies as $policy)
                                <option value="{{ $policy->Id }}" @selected(old('PolicyID') == $policy->Id)>{{ $policy->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Incident Date</label>
                        <input type="date" name="IncidentDate" class="form-control" value="{{ old('IncidentDate') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Reported Date</label>
                        <input type="date" name="ReportedDate" class="form-control" value="{{ old('ReportedDate') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Case Description</label>
                        <textarea name="Description" class="form-control" rows="3">{{ old('Description') }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Evidence Documents</label>
                        <input type="file" name="EvidenceFiles[]" class="form-control" multiple>
                        <small class="text-muted">Attach supporting evidence if available.</small>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('hr.discipline.cases.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Case</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    (function () {
        const employeeSelect = document.getElementById('caseEmployeeSelect');
        const complainantSelect = document.getElementById('caseComplainantSelect');
        if (!employeeSelect || !complainantSelect) {
            return;
        }

        const baseOptions = Array.from(complainantSelect.options).map((option) => ({
            value: option.value,
            text: option.text,
        }));

        function rebuildComplainants() {
            const selectedEmployee = employeeSelect.value;
            const currentValue = complainantSelect.value;
            complainantSelect.innerHTML = '';

            baseOptions.forEach((option) => {
                if (option.value && option.value === selectedEmployee) {
                    return;
                }
                const opt = document.createElement('option');
                opt.value = option.value;
                opt.textContent = option.text;
                complainantSelect.appendChild(opt);
            });

            if (currentValue && currentValue !== selectedEmployee) {
                complainantSelect.value = currentValue;
            }
        }

        employeeSelect.addEventListener('change', rebuildComplainants);
        rebuildComplainants();
    })();
</script>
@endsection
