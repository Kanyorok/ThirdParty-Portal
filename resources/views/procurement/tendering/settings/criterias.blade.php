@extends('layouts.app')

@section('title', 'Criteria for ' . $section->SectionName)

@section('content')
    <div class="container-fluid py-4">

        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createCriteriaModal">
            <i class="fa fa-plus-circle me-2"></i> Add Criteria
        </button>

        <table class="table table-striped">
            <thead>
            <tr>
                <th>Criteria Names</th>
                <th>Description</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($section->criteria as $criterion)
                <tr>
                    <td>{{ $criterion->CriteriaName }}</td>
                    <td>{{ $criterion->Description ?? '-' }}</td>
                    <td>
                        @if ($criterion->IsActive)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                        @if (isset($section) && isset($criterion))
                            <a href="{{ route('prequalification.sections.criteria.edit', [$section->Id, $criterion->Id]) }}"
                               class="btn btn-warning btn-sm">Edit</a>
                            <form
                                action="{{ route('prequalification.sections.criteria.destroy', [$section->Id, $criterion->Id]) }}"
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
                <form action="{{ route('prequalification.sections.criteria.store', $section->Id) }}" method="POST">
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
                                        <select name="IsActive" class="form-select" required>
                                            <option value="1">Active</option>
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
