@extends('layouts.app')
@section('title', 'Maintenance Completion')

@php use Carbon\Carbon; @endphp

@section('content')
<div class="container mt-4" style="max-width: 1200px;">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('workcompletion.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Log Completion
        </a>
    </div>

    <p class="text-muted"><small>This screen displays the completion status of maintenance requests.</small></p>

    @if($workCompletions->count())
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Request Number</th>
                                <th>Completion Date</th>
                                <th>Work Done Summary</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($workCompletions as $workCompletion)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $workCompletion->request->request->RequestNumber ?? '-' }}</td>
                                <td>{{ $workCompletion->CompletionDate ? Carbon::parse($workCompletion->CompletionDate)->format('d/m/Y') : '-' }}</td>
                                <td>{{ $workCompletion->WorkDoneSummary ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $workCompletion->finalstatus?->Description == 'Completed' ? 'success' : 'secondary' }}">
                                        {{ $workCompletion->finalstatus->Description ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('workcompletion.show', $workCompletion->Id) }}" class="btn btn-sm btn-primary">View</a>
                                    <a href="{{ route('workcompletion.edit', $workCompletion->Id) }}" class="btn btn-sm btn-info">Edit</a>
                                    <form class="d-inline" action="{{ route('workcompletion.destroy', $workCompletion->Id) }}" method="POST" 
                                          onsubmit="return confirm('Are you sure you want to delete this work completion?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info">
            No maintenance work completion records found.
        </div>
    @endif
</div>
@endsection
