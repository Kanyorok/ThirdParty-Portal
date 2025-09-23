@extends('layouts.app')
@php use Carbon\Carbon; @endphp

@section('title', 'Maintenance Assignments')

@section('content')
<div class="container mt-4">

    {{-- Header Actions --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('assignrequest.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Assign Task
        </a>
    </div>

    <p><small class="text-muted">The list below shows all tasks that have been assigned.</small></p>

    @if($assignments->count())
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-hover table-striped table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Request Number</th>
                            <th>Assignment Type</th>
                            <th>Expected Start</th>
                            <th>Expected Completion</th>
                            <th>Priority Level</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assignments as $assignment)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $assignment->request->RequestNumber ?? 'N/A' }}</td>
                                <td>{{ $assignment->assignmentType->Description ?? 'N/A' }}</td>
                                <td>{{ Carbon::parse($assignment->ExpectedStartDate)->format('d/m/Y') }}</td>
                                <td>{{ Carbon::parse($assignment->ExpectedCompletion)->format('d/m/Y') }}</td>
                                <td>{{ $assignment->priorityLevel->Description ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $assignment->Status->badgeColor() }}">
                                        {{ $assignment->Status->label() }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center flex-wrap">
                                        <a href="{{ route('assignrequest.show', $assignment->Id) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        <a href="{{ route('assignrequest.edit', $assignment->Id) }}" 
                                           class="btn btn-sm btn-info text-white">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <a href="{{ route('workcompletion.create') }}" 
                                           class="btn btn-sm btn-success">
                                            <i class="bi bi-check-circle"></i> Complete
                                        </a>

                                        @if($assignment->taskcompletion()->exists())
                                            <button class="btn btn-sm btn-secondary" disabled>
                                                <i class="bi bi-lock"></i> In Use
                                            </button>
                                        @else
                                            <form action="{{ route('assignrequest.destroy', $assignment->Id) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Are you sure you want to delete this maintenance assignment?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="alert alert-info mt-3">
            No maintenance assignment requests found.
        </div>
    @endif
</div>
@endsection
