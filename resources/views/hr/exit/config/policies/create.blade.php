@extends('layouts.app')

@section('title', 'New Exit Policy')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Create Exit Policy</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.config.exit-policies.index') }}">Back</a>
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

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form action="{{ route('hr.config.exit-policies.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name *</label>
                        <input type="text" name="Name" class="form-control" value="{{ old('Name') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Effective From</label>
                        <input type="date" name="EffectiveFrom" class="form-control" value="{{ old('EffectiveFrom') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Effective To</label>
                        <input type="date" name="EffectiveTo" class="form-control" value="{{ old('EffectiveTo') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Employment Types</label>
                        <input type="text" name="EmploymentTypes" class="form-control" value="{{ old('EmploymentTypes') }}" placeholder="Permanent, Contract">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contract Types</label>
                        <input type="text" name="ContractTypes" class="form-control" value="{{ old('ContractTypes') }}" placeholder="Fixed, Casual">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Approval Workflow</label>
                        <input type="text" name="ApprovalWorkflow" class="form-control" value="{{ old('ApprovalWorkflow') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Redundancy Criteria</label>
                        <input type="text" name="RedundancyCriteria" class="form-control" value="{{ old('RedundancyCriteria') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Terminal Dues Config</label>
                        <textarea name="TerminalDuesConfig" class="form-control" rows="3">{{ old('TerminalDuesConfig') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Checklist Template</label>
                        <select name="ChecklistTemplateID" class="form-select">
                            <option value="">Select</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->Id }}" @selected(old('ChecklistTemplateID') == $template->Id)>{{ $template->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="AllowNoticePay" value="1" checked>
                            <label class="form-check-label">Allow Notice Pay</label>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="AllowNoticeWaiver" value="1" checked>
                            <label class="form-check-label">Allow Notice Waiver</label>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="IsActive" value="1" checked>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Notice Periods</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addNoticeRow">+ Add Row</button>
                    </div>
                    <div class="card-body p-0">
                        <table class="table mb-0" id="noticeTable">
                            <thead>
                                <tr>
                                    <th>Employment Type</th>
                                    <th>Contract Type</th>
                                    <th>Notice Days</th>
                                    <th>Pay In Lieu</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="text" name="NoticeEmploymentType[]" class="form-control" placeholder="Permanent"></td>
                                    <td><input type="text" name="NoticeContractType[]" class="form-control" placeholder="Fixed"></td>
                                    <td><input type="number" name="NoticeDays[]" class="form-control" value="30"></td>
                                    <td class="text-center"><input type="checkbox" name="NoticePayInLieu[]" value="1" checked></td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-row">Remove</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('hr.config.exit-policies.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Policy</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        const tableBody = document.querySelector('#noticeTable tbody');
        const addBtn = document.getElementById('addNoticeRow');

        function bindRemove(row) {
            const btn = row.querySelector('.remove-row');
            btn.addEventListener('click', () => {
                if (tableBody.querySelectorAll('tr').length > 1) {
                    row.remove();
                }
            });
        }

        addBtn.addEventListener('click', () => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><input type="text" name="NoticeEmploymentType[]" class="form-control" placeholder="Permanent"></td>
                <td><input type="text" name="NoticeContractType[]" class="form-control" placeholder="Fixed"></td>
                <td><input type="number" name="NoticeDays[]" class="form-control" value="30"></td>
                <td class="text-center"><input type="checkbox" name="NoticePayInLieu[]" value="1" checked></td>
                <td class="text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-row">Remove</button>
                </td>
            `;
            tableBody.appendChild(row);
            bindRemove(row);
        });

        tableBody.querySelectorAll('tr').forEach(bindRemove);
    })();
</script>
@endsection
