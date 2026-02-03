@extends('layouts.app')
@section('title', 'Maintenance Completion')

@php
    use Carbon\Carbon;
@endphp

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
    .action-buttons {
        display: flex;
        flex-wrap: nowrap;
        gap: 0.4rem;
        align-items: center;
        justify-content: center;
    }
</style>
@endsection

@section('content')
<div class="container mt-4" style="max-width: 1200px;">

    <!-- Header -->
    <div class="d-flex justify-content-end align-items-center mb-3">
        <a href="{{ route('workcompletion.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle me-1"></i> Log Completion
        </a>
    </div>

    <p class="text-muted mb-3">
        <small>This table displays all maintenance tasks and their completion details.</small>
    </p>

    @if($workCompletions->count())
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="WorkCompletionTable"
                           class="table table-bordered table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:5%">#</th>
                                <th>Request No.</th>
                                <th>Completion Date</th>
                                <th>Work Summary</th>
                                <th>Status</th>
                                <th class="text-center" style="width:20%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($workCompletions as $workCompletion)
                                @php
                                    $status = strtolower($workCompletion->finalstatus->Description ?? 'pending');
                                    $isCompleted = $status === 'completed';
                                @endphp

                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td>
                                        {{ $workCompletion->request->request->RequestNumber ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $workCompletion->CompletionDate
                                            ? Carbon::parse($workCompletion->CompletionDate)->format('d M Y')
                                            : '-' }}
                                    </td>

                                    <td>
                                        {{ Str::limit($workCompletion->WorkDoneSummary ?? '-', 60) }}
                                    </td>

                                    <td>
                                        @switch($status)
                                            @case('completed')
                                                <span class="badge bg-success">Completed</span>
                                                @break
                                            @case('in progress')
                                                <span class="badge bg-info text-dark">In Progress</span>
                                                @break
                                            @case('pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-danger">Cancelled</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ ucfirst($status) }}</span>
                                        @endswitch
                                    </td>

                                    <!-- Actions -->
                                    <td class="text-center">
                                        <div class="action-buttons">

                                            {{-- View --}}
                                            <a href="{{ route('workcompletion.show', $workCompletion->Id) }}"
                                               class="btn btn-sm btn-primary"
                                               title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            @if($isCompleted)
                                                {{-- Locked / In Use --}}
                                                <button class="btn btn-sm btn-secondary"
                                                        title="This record is completed and locked">
                                                    <i class="bi bi-lock"></i>
                                                </button>
                                            @else
                                                {{-- Edit --}}
                                                <a href="{{ route('workcompletion.edit', $workCompletion->Id) }}"
                                                   class="btn btn-sm btn-warning"
                                                   title="Edit Record">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>

                                                {{-- Delete --}}
                                                <form action="{{ route('workcompletion.destroy', $workCompletion->Id) }}"
                                                      method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this work completion record?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-sm btn-danger"
                                                            title="Delete Record">
                                                        <i class="bi bi-trash"></i>
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
        </div>
    @else
        <div class="alert alert-info mt-3">
            <i class="bi bi-info-circle me-2"></i>
            No maintenance work completion records found.
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#WorkCompletionTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            responsive: true
        });
    });
</script>
@endsection
