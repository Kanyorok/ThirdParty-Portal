@extends('layouts.app')

@section('title', 'New Exit Request')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">New Exit Request</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.exit.requests.index') }}">Back</a>
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
            <form action="{{ route('hr.exit.requests.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Employee *</label>
                        <select name="EmployeeID" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->Id }}" @selected(old('EmployeeID') == $employee->Id)>
                                    {{ $employee->FirstName }} {{ $employee->LastName }} ({{ $employee->EmployeeNo }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Exit Type *</label>
                        <select name="ExitTypeID" id="exitTypeSelect" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($exitTypes as $exitType)
                                <option value="{{ $exitType->Id }}"
                                    data-requires-case="{{ $exitType->RequiresCase ? 1 : 0 }}"
                                    data-is-redundancy="{{ $exitType->IsRedundancy ? 1 : 0 }}"
                                    data-employer="{{ $exitType->IsEmployerInitiated ? 1 : 0 }}"
                                    @selected(old('ExitTypeID') == $exitType->Id)>
                                    {{ $exitType->Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Policy</label>
                        <select name="PolicyID" class="form-select">
                            <option value="">Select</option>
                            @foreach($policies as $policy)
                                <option value="{{ $policy->Id }}" @selected(old('PolicyID') == $policy->Id)>
                                    {{ $policy->Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Initiator Type *</label>
                        <select name="InitiatorType" id="initiatorType" class="form-select" required>
                            @foreach(['Employee','Employer'] as $type)
                                <option value="{{ $type }}" @selected(old('InitiatorType', 'Employee') === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Requested On</label>
                        <input type="date" name="RequestedOn" class="form-control" value="{{ old('RequestedOn') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Notice Date</label>
                        <input type="date" name="NoticeDate" class="form-control" value="{{ old('NoticeDate') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Proposed Last Day</label>
                        <input type="date" name="ProposedLastDay" class="form-control" value="{{ old('ProposedLastDay') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Effective Exit Date</label>
                        <input type="date" name="EffectiveExitDate" class="form-control" value="{{ old('EffectiveExitDate') }}">
                    </div>
                    <div class="col-md-4" id="caseBlock">
                        <label class="form-label">Related Case</label>
                        <select name="CaseID" class="form-select">
                            <option value="">Select</option>
                            @foreach($cases as $case)
                                <option value="{{ $case->Id }}" @selected(old('CaseID') == $case->Id)>
                                    {{ $case->CaseNo }} ({{ $case->Status }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4" id="redundancyBlock">
                        <label class="form-label">Redundancy Ref</label>
                        <select name="RedundancyID" class="form-select">
                            <option value="">Select</option>
                            @foreach($redundancies as $redundancy)
                                <option value="{{ $redundancy->Id }}" @selected(old('RedundancyID') == $redundancy->Id)>
                                    {{ $redundancy->RefNo }} ({{ $redundancy->Status }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Reason</label>
                        <textarea name="Reason" class="form-control" rows="3">{{ old('Reason') }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="NoticePayInLieu" value="1" id="noticePayInLieu" @checked(old('NoticePayInLieu'))>
                            <label class="form-check-label" for="noticePayInLieu">Pay in lieu of notice</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="NoticeWaived" value="1" id="noticeWaived" @checked(old('NoticeWaived'))>
                            <label class="form-check-label" for="noticeWaived">Notice waived</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('hr.exit.requests.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" name="Action" value="draft" class="btn btn-outline-secondary">Save Draft</button>
                    <button type="submit" name="Action" value="submit" class="btn btn-primary">Submit for Approval</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        const exitTypeSelect = document.getElementById('exitTypeSelect');
        const initiatorSelect = document.getElementById('initiatorType');
        const caseBlock = document.getElementById('caseBlock');
        const redundancyBlock = document.getElementById('redundancyBlock');

        function updateExitTypeFields() {
            const option = exitTypeSelect.options[exitTypeSelect.selectedIndex];
            const requiresCase = option?.dataset?.requiresCase === '1';
            const isRedundancy = option?.dataset?.isRedundancy === '1';
            const employer = option?.dataset?.employer === '1';

            caseBlock.style.display = requiresCase ? 'block' : 'none';
            redundancyBlock.style.display = isRedundancy ? 'block' : 'none';

            if (employer) {
                initiatorSelect.value = 'Employer';
            }
        }

        exitTypeSelect.addEventListener('change', updateExitTypeFields);
        updateExitTypeFields();
    })();
</script>
@endsection
