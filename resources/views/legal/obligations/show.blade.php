@extends('layouts.app')
@section('title', 'View Legal Obligation')

@section('content')
<div class="card shadow rounded-4 border-0">
    <div class="card-header bg-light px-3 py-2 d-flex justify-content-between align-items-center">
        <h5 class="text-info mb-0"><i class="fas fa-gavel"></i> Legal Obligation</h5>
        <div>
            <a href="{{ route('legal.obligations.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-long-arrow-alt-left"></i> Back
            </a>
            <!-- Button to open Assign User Modal -->
            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#assignUserModal-{{ $obligation->Id }}">
                <i class="fas fa-user-plus"></i> Assign User
            </button>
        </div>
    </div>

    <div class="card-body">
        <p class="text-muted">Overview of the obligation details and important deadlines.</p>

        <div class="row mb-3">
            <div class="col-md-6">
                <div class="p-3 bg-light rounded-3">
                    <h6 class="text-info mb-1">Title:   </h6>
                    <p class="mb-0 fw-semibold">{{ $obligation->Title }}</p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="p-3 bg-light rounded-3">
                    <h6 class="text-info mb-1">Source Type:</h6>
                    <p class="mb-0 fw-semibold">{{ $obligation->SourceType ?? '—' }}</p>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <div class="p-3 bg-light rounded-3">
                    <h6 class="text-info mb-1">Due Date:</h6>
                    <p class="mb-0 fw-semibold">
                        {{ $obligation->DueDate ? \Carbon\Carbon::parse($obligation->DueDate)->format('d M Y') : '—' }}
                    </p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="p-3 bg-light rounded-3">
                    <h6 class="text-info mb-1">Status:</h6>
                    <p class="mb-0 fw-semibold">
                        @if($obligation->Status === 'Overdue')
                            <span class="badge bg-danger">Overdue</span>
                        @elseif($obligation->Status === 'Pending')
                            <span class="badge bg-warning">Pending</span>
                        @elseif($obligation->Status === 'Completed')
                            <span class="badge bg-success">Completed</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="p-3 bg-light rounded-3 mb-3">
            <h6 class="text-info mb-1">Description:</h6>
            <p class="mb-0">{{ $obligation->Description ?? '—' }}</p>
        </div>

        {{-- ✅ Show Assignee only if exists --}}
        @if($obligation->AssignedTo)
            <div class="p-3 bg-light rounded-3 mb-3">
                <h6 class="text-info mb-1">Assigned To:</h6>
                <p class="mb-0 fw-semibold">
                    {{ $obligation->users->Name }} ({{ $obligation->users->Email }})
                </p>
            </div>
        @endif
    </div>
</div>

<!-- Assign User Modal -->
<form method="POST" action="{{ route('legal.obligations.assignUser', $obligation->Id) }}">
    @csrf
    @method('PATCH')
    <div class="modal fade" id="assignUserModal-{{ $obligation->Id }}" tabindex="-1" aria-labelledby="assignUserModalLabel-{{ $obligation->Id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="assignUserModalLabel-{{ $obligation->Id }}">
                        <i class="fas fa-user-plus"></i> Assign User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="UserId-{{ $obligation->Id }}" class="form-label">Select User</label>
                        <select class="form-select" id="UserId-{{ $obligation->Id }}" name="UserId" required>
                            <option value="" disabled {{ !$obligation->UserId ? 'selected' : '' }}>-- Choose User --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->Id }}" {{ $obligation->UserId == $user->Id ? 'selected' : '' }}>
                                    {{ $user->Name }} ({{ $user->Email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" onclick="if(this.form.checkValidity()){this.disabled=true; this.innerText='Assigning...'; this.form.submit();}">Assign</button>
                </div>

            </div>
        </div>
    </div>
</form>
@endsection
