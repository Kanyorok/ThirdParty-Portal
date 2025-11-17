@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'Lease Terminations')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        .action-buttons {
            display: flex;
            flex-wrap: nowrap;
            gap: 0.4rem;
            align-items: center;
        }
    </style>
@endsection

@section('content')
<div class="container mt-4">

    <!-- Header -->
    <div class="d-flex justify-content-end align-items-center mb-3">
        <a href="{{ route('terminatelease.create') }}" class="btn btn-primary">
            <i class="bi bi-x-circle me-1"></i> Terminate Lease
        </a>
    </div>

    <p class="text-muted">
        <small>This table lists all terminated lease agreements, along with their reasons and remarks.</small>
    </p>

    @if($leaseterminations->count())
        <div class="card shadow-sm">
            <div class="card-body">
                <table id="LeaseTermination" class="table table-bordered table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%">#</th>
                            <th>Lease Number</th>
                            <th>Termination Date</th>
                            <th>Reason</th>
                            <th>Remarks</th>
                            <th style="width: 10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaseterminations as $leasetermination)
                            <tr>
                                <td>{{ $loop->iteration ?? '-' }}</td>
                                <td>{{ $leasetermination->lease->LeaseNumber ?? '-' }}</td>
                                <td>{{ $leasetermination->TerminationDate ? Carbon::parse($leasetermination->TerminationDate)->format('d/m/Y') : '-' }}</td>
                                <td>{{ $leasetermination->code->Description ?? '-' }}</td>
                                <td>{{ $leasetermination->Remarks ?? '-' }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('terminatelease.show', $leasetermination->Id) }}"
                                           class="btn btn-sm btn-outline-secondary" title="View Termination Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
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
            <i class="bi bi-info-circle me-2"></i> No lease terminations have been registered yet.
        </div>
    @endif

</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#LeaseTermination').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection
