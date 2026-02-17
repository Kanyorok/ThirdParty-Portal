@extends('layouts.app')

@section('title', 'Investigation')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Investigation</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.discipline.cases.show', $case->Id) }}">Back</a>
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

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form action="{{ route('hr.discipline.cases.investigation.store', $case->Id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Investigator Type *</label>
                        <select name="InvestigatorType" class="form-select" id="investigatorType" required>
                            @foreach(['Internal','External'] as $type)
                                <option value="{{ $type }}" @selected(old('InvestigatorType', $investigation->InvestigatorType ?? 'Internal') === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5" id="internalInvestigatorBlock">
                        <label class="form-label">Investigator (Employee)</label>
                        <select name="InvestigatorID" class="form-select" id="investigatorEmployee">
                            <option value="">Select</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->Id }}" @selected(old('InvestigatorID', $investigation->InvestigatorID ?? null) == $employee->Id)>
                                    {{ $employee->FirstName }} {{ $employee->LastName }} ({{ $employee->EmployeeNo }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4" id="externalInvestigatorBlock">
                        <label class="form-label">Investigator Name (external)</label>
                        <input type="text" name="InvestigatorName" class="form-control" id="investigatorExternalName" value="{{ old('InvestigatorName', $investigation->InvestigatorName ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="StartDate" class="form-control" value="{{ old('StartDate', $investigation?->StartDate?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="EndDate" class="form-control" value="{{ old('EndDate', $investigation?->EndDate?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <input type="text" name="Status" class="form-control" value="{{ old('Status', $investigation->Status ?? 'Draft') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="ConflictDeclared" value="1" @checked(old('ConflictDeclared', $investigation->ConflictDeclared ?? false))>
                            <label class="form-check-label">Conflict Declared</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Findings Summary</label>
                        <textarea name="FindingsSummary" class="form-control" rows="4">{{ old('FindingsSummary', $investigation->FindingsSummary ?? '') }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Investigation Report</label>
                        <input type="file" name="ReportDocument" class="form-control">
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Save Investigation</button>
                </div>
            </form>
            @if($investigation)
                <form action="{{ route('hr.discipline.cases.investigation.approve', $case->Id) }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-outline-success">Approve Report</button>
                </form>
            @endif
        </div>
    </div>

    @if($investigation)
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Investigation Documents</h5>
                <ul class="list-unstyled">
                    @forelse($documents as $doc)
                        <li>
                            @if($doc->document)
                                <a href="{{ route('file.preview', ['document' => $doc->document->DocumentId]) }}" target="_blank">
                                    {{ $doc->document->Name }}
                                </a>
                            @else
                                Document
                            @endif
                        </li>
                    @empty
                        <li class="text-muted">No documents uploaded.</li>
                    @endforelse
                </ul>

                <form action="{{ route('hr.discipline.cases.investigation.documents.store', $case->Id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-6">
                            <input type="file" name="Document" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="DocType" class="form-control" placeholder="Doc type">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-outline-primary">Upload Document</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

<script>
    (function () {
        const typeSelect = document.getElementById('investigatorType');
        const internalBlock = document.getElementById('internalInvestigatorBlock');
        const externalBlock = document.getElementById('externalInvestigatorBlock');
        const employeeSelect = document.getElementById('investigatorEmployee');
        const externalInput = document.getElementById('investigatorExternalName');

        function toggleInvestigatorFields() {
            const isExternal = typeSelect.value === 'External';
            internalBlock.style.display = isExternal ? 'none' : '';
            externalBlock.style.display = isExternal ? '' : '';
            employeeSelect.disabled = isExternal;
            externalInput.disabled = !isExternal;
            if (isExternal) {
                employeeSelect.value = '';
            } else {
                externalInput.value = '';
            }
        }

        toggleInvestigatorFields();
        typeSelect.addEventListener('change', toggleInvestigatorFields);
    })();
</script>
@endsection
