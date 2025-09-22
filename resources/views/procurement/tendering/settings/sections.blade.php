@extends('layouts.app')

@section('title', 'Sections Management')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSectionModal">
                <i class="fa fa-plus-circle me-2"></i> Add Section
            </button>
        </div>

        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th>Section Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($sections as $section)
                <tr>
                    <td>{{ $section->SectionName }}</td>
                    <td>{{ $section->Description ?? '-' }}</td>
                    <td>
                        @if ($section->IsActive)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                        @if (isset($section) && $section instanceof \App\Models\Procurement\Section)
                            <a href="{{ route('prequalification.sections.edit', $section->Id) }}"
                               class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('prequalification.sections.destroy', $section->Id) }}" method="POST"
                                  class="d-inline-block">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">Delete</button>
                            </form>
                            <a href="{{ route('prequalification.sections.show', $section->Id) }}"
                               class="btn btn-info btn-sm">Criteria</a>
                        @else
                            <!-- Section model missing or invalid; hide actions -->
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="createSectionModal" tabindex="-1" aria-labelledby="createSectionLabel"
         aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('prequalification.sections.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createSectionLabel">Add Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
          <div class="modal-body">
              <div class="mb-3">
                  <label for="SectionName" class="form-label">Section Name</label>
                  <input type="text" name="SectionName" class="form-control" required>
              </div>
            <div class="mb-3">
                <label for="Description" class="form-label">Description</label>
                <textarea name="Description" class="form-control"></textarea>
            </div>
            <div class="mb-3">
                <label for="IsActive" class="form-label">Status</label>
                <select name="IsActive" class="form-select" required>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
          </div>
          <div class="modal-footer">
              <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Create Section</button>
          </div>
            </div>
        </form>
    </div>
    </div>
@endsection
