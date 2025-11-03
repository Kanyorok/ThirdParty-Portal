@extends('layouts.app')
@section('title', 'Clause Library')

@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-centre mb-3">
        <h4 class="mb-0 text-info"><i class="fas fa-file-contract"></i> Clause / Template Library</h4>
        <a href="{{ route('legal.clauses.create') }}" class="btn btn-info"><i class="fas fa-plus me-1"></i> Add Clause</a>
    </div>
    <div class="card-body">
        <p class="text-muted">Browse, manage, and update standard legal clauses and templates for quick inclusion in contracts and legal documents.</p>
        <div class="table-reponsive">
        <table class="table table-hover table-sm align-middle text-center"
            style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Version</th>
                    <th>Standard</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @if($clauses->count())
                @foreach ($clauses as $clause)
                    <tr>
                        <td>{{ $clause->Title }}</td>
                        <td>{{ $clause->ClauseType }}</td>
                        <td>v {{ $clause->Version }}</td>
                        <td>
                            @if($clause->IsStandard === 'Yes')
                                <span class="badge bg-success">Yes</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </td>
                        <td>
                            {{-- <a href="{{ route('legal.clauses.edit', $clause->ID) }}" class="btn btn-sm btn-info">✏️ Edit</a> --}}
                            <a href="{{ route('legal.clauses.show', $clause->Id) }}" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('legal.clauses.edit', $clause->Id) }}" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>
                            <button type="button"
                                class="btn btn-sm btn-danger custom-delete-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#customDeleteConfirmModal"
                                data-name="{{$clause->Title}}"
                                data-route="{{ route('legal.clauses.destroy', $clause->Id) }}"
                                title="Delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
                @else
                    <tr>
                        <td colspan="5" class="p-0">
                            <div class="text-centre p-4 border rounded-3 bg-light">
                                <p class="mb-3 text-muted fs-5">
                                    <i class="fas fa-info-circle me-2 text-info"></i>
                                    <i>No Clauses Added.</i>
                                </p>
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
        </div>
        <div class="d-flex justify-content-end align-items-center mt-3">
            {{ $clauses->links() }}
        </div>
    </div>
</div>
@include('components.modals.delete-confirm')
@endsection
