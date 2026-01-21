@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'Scheduled Leases')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <style>
        .action-buttons {
            display: flex;
            flex-wrap: nowrap;
            gap: 0.4rem;
            align-items: center;
        }
        .action-buttons form {
            margin: 0;
        }

        /* Word wrap inside table cells */
        table td, table th {
            white-space: normal !important;
            word-wrap: break-word;
            word-break: break-word;
        }

        /* For scrollable table */
        .table-responsive {
            overflow-x: auto;
        }
    </style>
@endsection

@section('content')
<div class="container mt-4">

    <!-- Page Header -->
    <div class="d-flex justify-content-end align-items-center mb-3">
        <a href="{{ route('schedulelease.create') }}" class="btn btn-success">
            <i class="bi bi-calendar-plus me-1"></i> Generate Schedule
        </a>
    </div>

    <p class="text-muted">
        <small>This screen displays all lease schedules — whether manually created or auto-generated.</small>
    </p>

    @if($leaseschedules->count())
        <div class="card shadow-sm">
            <div class="card-body">

                <!-- Scrollable Table -->
                <div class="table-responsive">
                    <table id="LeaseSchedule" class="table table-bordered table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%">#</th>
                                <th>Lease Number</th>
                                <th>Tenant Name</th>
                                <th>Property Leased</th>
                                <th>Payment Frequency</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th style="width: 10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaseschedules as $leaseschedule)
                                <tr>
                                    <td>{{ $loop->iteration ?? '-' }}</td>
                                    <td>{{ $leaseschedule->lease->LeaseNumber ?? '-' }}</td>
                                    <td>{{ $leaseschedule->lease->tenant->thirdParty->ThirdPartyName ?? '-' }}</td>
                                    <td>{{ $leaseschedule->lease->property->PropertyName ?? '-' }}</td>
                                    <td>{{ $leaseschedule->paymentFrequency->Description ?? '-' }}</td>
                                    <td>{{ $leaseschedule->StartDate ? Carbon::parse($leaseschedule->StartDate)->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $leaseschedule->EndDate ? Carbon::parse($leaseschedule->EndDate)->format('d/m/Y') : '-' }}</td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('schedulelease.show', $leaseschedule->Id) }}"
                                               class="btn btn-sm btn-info text-white" title="View Schedule">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <a href="{{ route('schedulelease.print', $leaseschedule->Id) }}"
                                               target="_blank" class="btn btn-sm btn-secondary" title="Print Schedule">
                                                <i class="bi bi-printer"></i>
                                            </a>

                                            <a href="{{ route('schedulelease.edit', $leaseschedule->Id) }}"
                                               class="btn btn-sm btn-warning" title="Edit Schedule">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <form action="{{ route('schedulelease.destroy', $leaseschedule->Id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Are you sure you want to delete this lease schedule?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete Schedule">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- End Scrollable Table -->

            </div>
        </div>
    @else
        <div class="alert alert-info mt-3">
            <i class="bi bi-info-circle me-2"></i> No lease schedules registered yet.
        </div>
    @endif

</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#LeaseSchedule').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            scrollX: true   // Enable horizontal scrolling
        });
    });
</script>
@endsection
