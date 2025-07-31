@extends('layouts.app')
@section('title', 'Approval Workflow Details')

@section('styles')
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.6.2/dist/select2-bootstrap4.min.css"
    rel="stylesheet" />
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
            <input type="text" name="StageName" placeholder="e.g. Committee Stage" class="form-control" required
              value="{{ old('StageName') }}">
          </div>

          <div class="col-md-4">
            <label for="EscalationLimit" class="form-label">Escalation Limit</label>
            <input type="number" name="EscalationLimit" class="form-control" required min="1"
              placeholder="e.g. 3 – Days before escalation" value="{{ old('EscalationLimit') }}">
          </div>

          <div class="col-md-4">
            <label for="TypeID" class="form-label">Approval Type</label>
            <select name="TypeID" id="TypeID" class="form-select" required>
              <option value="">-- Select Type --</option>
              @foreach ($approvalTypes as $type)
                <option value="{{ $type->Id }}" data-code="{{ $type->TypeID }}">
                  {{ $type->TypeID }} - {{ $type->Name }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="row mb-3" id="limitGroup" style="display: none;">
          <div class="col-md-4">
            <label for="WorkflowLimitID" class="form-label">Approval Limit (AMOUNT only)</label>
            <select name="WorkflowLimitID" id="WorkflowLimitID" class="form-control select2">
              <option value="">Select WorkFlow Limit</option>
              @foreach ($workflowLimits as $limit)
                <option value="{{ $limit->Id }}">{{ $limit->Source }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="row mb-3" id="countGroup" style="display: none;">
          <div class="col-md-4">
            <label for="Count" class="form-label">Approver Count (COUNT only)</label>
            <input type="number" name="Count" id="Count" class="form-control" min="1"
              value="{{ old('Count') }}">
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-4">
            <label for="PermissionName" class="form-label">Permission Name</label>
            <select name="Permission" id="PermissionSelect" class="form-control select2" required>
              <option value="">Select Permission</option>
              @foreach ($permissions as $permission)
                <option value="{{ $permission->id }}">{{ $permission->name }}</option>
              @endforeach
            </select>
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
          @forelse ($stages as $index => $stage)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>{{ $stage->StageName }}</td>
              <td>{{ $stage->type_name ?? $stage->TypeID }}</td>
              <td>{{ $stage->role_name ?? '-' }}</td>
              <td>{{ $stage->MaxAmount ?? '-' }}</td>
              <td>{{ $stage->IsFinalStage ? 'Yes' : 'No' }}</td>
              <td>
                {{-- Add delete/edit buttons here if needed --}}
                <button class="btn btn-sm btn-danger">🗑️</button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted">No stages added yet.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endsection

@section('scripts')
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script>
    $(document).ready(function() {
      function toggleFields() {
        const selected = $('#TypeID').find(':selected').data('code');
        $('#limitGroup').toggle(selected === 'AMT');
        $('#countGroup').toggle(selected === 'CNT');
      }

      $('#TypeID').on('change', toggleFields);
      toggleFields(); // initial

      $('.select2').select2({
        theme: 'bootstrap4',
        placeholder: '🔍 Type to search...',
        allowClear: true,
        width: '100%',
        minimumInputLength: 1
      });
    });
  </script>
@endsection
