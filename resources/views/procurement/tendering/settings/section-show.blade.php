@extends('layouts.app')

@section('title', 'Criteria for ' . $section->SectionName)

@section('content')
  <div class="container-fluid py-4">
    <h4 class="text-primary">Criteria for {{ $section->SectionName }}</h4>

    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createCriteriaModal">
      <i class="fa fa-plus-circle me-2"></i> Add Criteria
    </button>

    <table class="table table-striped">
      <thead>
        <tr>
          <th>Criteria Name</th>
          <th>Description</th>
          <th>Status</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($section->criteria as $criterion)
          <tr>
            @if (isset($section) && isset($criterion))
              <form action="{{ route('prequalification.sections.criteria.update', [$section, $criterion]) }}"
                method="POST">
                @csrf
                @method('PUT')
                <td class="align-middle">
                  <input type="text" name="CriteriaName" class="form-control form-control-sm"
                    value="{{ old('CriteriaName', $criterion->CriteriaName) }}" required>
                </td>
                <td class="align-middle">
                  <input type="text" name="Description" class="form-control form-control-sm"
                    value="{{ old('Description', $criterion->Description) }}">
                </td>
                <td class="align-middle">
                  <select name="IsActive" class="form-select form-select-sm">
                    <option value="1" {{ old('IsActive', $criterion->IsActive) == 1 ? 'selected' : '' }}>Active
                    </option>
                    <option value="0" {{ old('IsActive', $criterion->IsActive) == 0 ? 'selected' : '' }}>Inactive
                    </option>
                  </select>
                </td>
                <td class="text-end align-middle">
                  <button type="submit" class="btn btn-warning btn-sm me-1">Update</button>
              </form>
              <form action="{{ route('prequalification.sections.criteria.destroy', [$section, $criterion]) }}"
                method="POST" class="d-inline-block">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger btn-sm">Delete</button>
              </form>
            @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="modal fade" id="createCriteriaModal" tabindex="-1" aria-labelledby="createCriteriaLabel"
    aria-hidden="true">
    <div class="modal-dialog">
      @if (isset($section))
        <form action="{{ route('prequalification.sections.criteria.store', $section) }}" method="POST">
        @else
          <form action="#" method="POST">
      @endif
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="createCriteriaLabel">Add Criteria</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="CriteriaName" class="form-label">Criteria Name</label>
            <input type="text" name="CriteriaName" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="Description" class="form-label">Description</label>
            <textarea name="Description" class="form-control"></textarea>
          </div>
          <div class="mb-3">
            <label for="IsActive" class="form-label">Status</label>
            <select name="IsActive" class="form-select">
              <option value="1" selected>Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Create Criteria</button>
        </div>
      </div>
      </form>
    </div>
  </div>
@endsection
