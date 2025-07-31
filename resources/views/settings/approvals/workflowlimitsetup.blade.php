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
  <div class="card shadow p-4 rounded-4 mb-4">
    <h4 class="mb-4">➕ Setup Workflow Limit</h4>

    <form method="POST" action="{{ route('settings.approval_workflow_limit.store') }}">
      @csrf
      <div class="row mb-3">
        <div class="col-md-3">
          <label for="DocType" class="form-label">Document Type <span class="text-danger">*</span></label>
          <select name="DocType" id="DocType" class="form-control select2" required>
            <option value="">-- Select --</option>
            @foreach ($sourceOptions as $alias => $class)
              <option value="{{ $class }}">{{ class_basename($class) }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label for="PermissionName" class="form-label">Permission Name</label>
          <select name="Permission" id="PermissionSelect" class="form-control select2" required>
            <option value="">Select Permission</option>
            @foreach ($permissions as $permission)
              <option value="{{ $permission->id }}">{{ $permission->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3">
          <label for="AmountLimit" class="form-label">Max Amount</label>
          <input type="number" name="AmountLimit" id="AmountLimit" class="form-control" min="1"
            value="{{ old('AmountLimit') }}" required>
        </div>
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
            <th>Document Type</th>
            <th>Max Amount</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($limits as $limit)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $limit->Source ?? 'N/A' }}</td>
              <td>{{ number_format($limit->MaxAmount, 2) }}</td>
              <td>
                {{-- Edit/Delete buttons (optional) --}}
                <form method="POST" action="#"
                  onsubmit="return confirm('Are you sure you want to delete this limit?')">
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
  @endif
@endsection

@section('scripts')
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#PermissionSelect').select2({
        theme: 'bootstrap4',
        placeholder: '🔍 Type to search permission...',
        allowClear: true,
        width: '100%',
        minimumInputLength: 1
      });
    });

    $(document).ready(function() {
      $('#DocType').select2({
        theme: 'bootstrap4',
        placeholder: '🔍 Type to search document...',
        allowClear: true,
        width: '100%',
        minimumInputLength: 1
      });
    });
  </script>
@endsection
