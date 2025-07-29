@extends('layouts.app')
@section('title', 'Approval Workflow Details')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.6.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="card shadow p-4 rounded-4">
    <h4 class="mb-4">👁️ View Approval Workflow</h4>

    <dl class="row">
        <dt class="col-sm-3">Name</dt>
        <dd class="col-sm-9">{{ $approval->Name }}</dd>

        <dt class="col-sm-3">Description</dt>
        <dd class="col-sm-9">{{ $approval->Description }}</dd>

        <dt class="col-sm-3">Document Type</dt>
        <dd class="col-sm-9">{{ class_basename($approval->Source) }}</dd>

        <dt class="col-sm-3">Created By</dt>
        <dd class="col-sm-9">{{ optional($approval->createdByUser)->Name ?? 'N/A' }}</dd>

        <dt class="col-sm-3">Created On</dt>
        <dd class="col-sm-9">{{ \Carbon\Carbon::parse($approval->CreatedOn)->format('d-m-Y H:i') }}</dd>
    </dl>

    <hr>

    <div class="card shadow p-4 rounded-4 mt-4">
        <h4 class="mb-4">➕ Add Approval Stage</h4>

        <form method="POST" action="#" id="stageForm">
            @csrf
            <input type="hidden" name="WorkflowID" value="{{ $approval->Id }}">

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="StageName" class="form-label">Stage Name</label>
                    <input type="text" name="StageName"  placeholder="e.g. Committee Stage" class="form-control" required value="{{ old('StageName') }}">
                </div>

                <div class="col-md-4">
                    <label for="EscalationLimit" class="form-label">Escalation Limit</label>
                    <input type="number" name="EscalationLimit" class="form-control"
                        required min="1"
                        placeholder="e.g. 3 – Days before escalation"
                        value="{{ old('EscalationLimit') }}">
                </div>

                <div class="col-md-4">
                    <label for="TypeID" class="form-label">Approval Type</label>
                    <select name="TypeID" id="TypeID" class="form-select" required>
                        <option value="">-- Select Type --</option>
                        @foreach($approvalTypes as $type)
                            <option value="{{ $type->Id }}">{{ $type->TypeID }} - {{ $type->Name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mb-3" id="limitGroup" style="display: none;">
                <div class="col-md-4">
                    <label for="WorkflowLimitID" class="form-label">Approval Limit (AMOUNT only)</label>
                    <select name="WorkflowLimitID" class="form-select">
                        <option value="">-- Select Limit --</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3" id="countGroup" style="display: none;">
                <div class="col-md-4">
                    <label for="Count" class="form-label">Approval Count (COUNT only)</label>
                    <input type="number" name="Count" id="Count" class="form-control" min="1" value="{{ old('Count') }}">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="PermissionSelect" class="form-label">Permission to Approve</label>
                    <select name="PermissionID" id="PermissionSelect" class="form-select select2" required>
                        <option value="">-- Search & Select Permission --</option>
                        @foreach($permissions as $permission)
                        <option value="{{ $permission->id }}">{{ $permission->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="Count" class="form-label">Approver Count</label>
                    <input type="number" name="Count" id="Count" class="form-control" min="1" value="{{ old('Count') }}">
                </div>

                <div class="col-md-4 d-flex align-items-end pt-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" name="IsFinalStage" id="IsFinalStage">
                        <label class="form-check-label" for="IsFinalStage">
                            Mark as Final Stage
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">💾 Save Stage</button>
                <a href="{{ route('settings.approval_stages.show', $approval->Id) }}" class="btn btn-secondary">← Back</a>
            </div>
        </form>
    </div>
    <div class="mt-5">
        <h5 class="mb-3">🧾 Added Stages</h5>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Role</th>
                    <th>Cut-off</th>
                    <th>Final</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(item, index) in stages" :key="index">
                    <tr>
                        <td x-text="index + 1"></td>
                        <td x-text="item.name"></td>
                        <td x-text="approvalTypes[item.type] ?? item.type"></td>
                        <td x-text="item.role || '-'"></td>
                        <td x-text="item.cutoff ? '$' + item.cutoff : '-'"></td>
                        <td x-text="item.isFinal ? 'Yes' : 'No'"></td>
                        <td>
                            <button class="btn btn-sm btn-danger" @click="removeStage(index)">🗑️</button>
                        </td>
                    </tr>
                </template>
                <tr x-show="stages.length === 0">
                    <td colspan="7" class="text-center text-muted">No stages added yet.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
</div>
@endsection

@section('styles')
<style>
    .select2-container--bootstrap4 .select2-selection--single {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        height: calc(2.375rem + 2px);
        /* matches Bootstrap form-select */
        padding: 0.375rem 0.75rem;
    }

    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        line-height: 1.5;
    }
</style>
@endsection


@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeField = document.getElementById('TypeID');
        const limitGroup = document.getElementById('limitGroup');
        const countGroup = document.getElementById('countGroup');

        function toggleFields() {
            const type = typeField.value;
            limitGroup.style.display = (type === 'AMT') ? 'flex' : 'none';
            countGroup.style.display = (type === 'CNT') ? 'flex' : 'none';
        }

        typeField.addEventListener('change', toggleFields);
        toggleFields(); // Run on load

        $('#PermissionSelect').select2({
            theme: 'bootstrap5',
            placeholder: '🔍 Type to search permission...',
            allowClear: true,
            width: '100%',
            minimumInputLength: 1
        });
    });
</script>
@endsection