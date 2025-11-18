@extends('layouts.app')
@section('title', 'Workflow Limit Setup')

@section('styles')
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <style>
    .select2-container--bootstrap4 .select2-selection--single {
      border: 1px solid #ced4da;
      border-radius: 0.375rem;
      height: calc(2.375rem + 2px);
      padding: 0.375rem 0.75rem;
    }

    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
      line-height: 1.5;
    }
  </style>
@endsection

@section('content')
  {{-- SUCCESS/ERROR MESSAGES --}}
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul>
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif
  @if (session('success'))
    <div class="alert alert-success">
      {{ session('success') }}
    </div>
  @endif
  
  <div class="card shadow p-4 rounded-4 mb-4">
    <h4 class="mb-4">➕ Setup Workflow Limit</h4>
    <form method="POST" action="{{ route('settings.workflow_limits.store') }}">
      @csrf
      <div class="row mb-3">
        <div class="col-md-4">
          <label for="WorkFlowStageId" class="form-label">Workflow Stage <span class="text-danger">*</span></label>
          <select name="WorkFlowStageId" id="WorkFlowStageId" class="form-control select2" required>
            <option value="">-- Select Workflow Stage --</option>
            @foreach ($workflowStages as $stage)
              @if (!in_array($stage->Id, $existingWorkflowStageIds))
                <option value="{{ $stage->Id }}" 
                        data-workflow="{{ $stage->workflow_name }}"
                        data-source="{{ $stage->workflow_source }}"
                        {{ old('WorkFlowStageId') == $stage->Id ? 'selected' : '' }}>
                  {{ $stage->StageName }} ({{ $stage->workflow_name }} - {{ $stage->workflow_source }})
                </option>
              @endif
            @endforeach
          </select>
          <small class="form-text text-muted">Only AMT workflow type stages without existing limits are shown</small>
          @error('WorkFlowStageId')
            <div class="text-danger">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-4">
          <label for="Permission" class="form-label">Permission Name <span class="text-danger">*</span></label>
          <select name="Permission" id="PermissionSelect" class="form-control select2" required>
            <option value="">Select Permission</option>
            @foreach ($permissions as $permission)
              <option value="{{ $permission->id }}" {{ old('Permission') == $permission->id ? 'selected' : '' }}>
                {{ $permission->name }}
              </option>
            @endforeach
          </select>
          @error('Permission')
            <div class="text-danger">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-4">
          <label for="AmountLimit" class="form-label">Max Amount <span class="text-danger">*</span></label>
          <input type="number" name="AmountLimit" id="AmountLimit" class="form-control" min="0.01" step="0.01"
            value="{{ old('AmountLimit') }}" required>
          @error('AmountLimit')
            <div class="text-danger">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="alert alert-info">
        <strong>Note:</strong> The selected permission will be created and linked to this workflow limit.
      </div>

      <button type="submit" class="btn btn-primary">💾 Save Workflow Limit</button>
    </form>
  </div>

  @if ($limits->count())
    <div class="card shadow p-4 rounded-4">
      <h5 class="mb-3">📋 Existing Workflow Limits</h5>

      <table class="table table-bordered table-hover">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Workflow Stage</th>
            <th>Workflow</th>
            <th>Source</th>
            <th>Max Amount</th>
            <th>Permission Name</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($limits as $limit)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $limit->workflow_stage->StageName ?? 'N/A' }}</td>
              <td>{{ $limit->workflow_stage->workflow->Name ?? 'N/A' }}</td>
              <td>{{ $limit->workflow_stage->workflow->Source ?? 'N/A' }}</td>
              <td>{{ number_format($limit->MaxAmount, 2) }}</td>
              <td>
                <code>{{ $limit->permission->name ?? 'N/A' }}</code>
              </td>
              <td>
                <form method="POST" action="{{ route('settings.workflow_limits.destroy', $limit->Id) }}"
                  onsubmit="return confirm('Are you sure you want to delete the limit for {{ $limit->workflow_stage->StageName ?? 'this stage' }}?')">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-danger">🗑️ Remove</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div class="card shadow p-4 rounded-4">
      <div class="alert alert-info">
        No workflow limits configured yet.
      </div>
    </div>
  @endif
@endsection

@section('scripts')
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script>
    $(document).ready(function() {
      // Initialize Workflow Stage Select2
      $('#WorkFlowStageId').select2({
        theme: 'bootstrap4',
        placeholder: '🔍 Type to search workflow stage...',
        allowClear: true,
        width: '100%',
        minimumInputLength: 0
      });

      // Initialize Permission Select2
      $('#PermissionSelect').select2({
        theme: 'bootstrap4',
        placeholder: '🔍 Type to search permission...',
        allowClear: true,
        width: '100%',
        minimumInputLength: 1
      });
    });
  </script>
@endsection