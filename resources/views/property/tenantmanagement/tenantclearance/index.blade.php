@php use Carbon\Carbon; @endphp
@extends('layouts.app')
@section('title', 'Tenant Exit')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container mt-4">

    <!-- Page Header -->
    <div class="d-flex justify-content-end align-items-center mb-3">
        <a href="{{ route('tenantclearance.create') }}" class="btn btn-primary">
            <i class="bi bi-door-open-fill me-1"></i> New Clearance
        </a>
    </div>

    <p class="text-muted">
        <small>This screen displays all tenants who have been cleared after lease termination.</small>
    </p>

    @if($clearancetenants->count())
        <div class="card shadow-sm">
            <div class="card-body">
                <table id="tenantclearance" class="table table-bordered table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th style="width: 5%">#</th>
                        <th>Tenant</th>
                        <th>Exit Date</th>
                        <th>Final Inspection</th>
                        <th>Dues Cleared</th>
                        <th>Keys Returned</th>
                        <th>Deposit Status</th>
                        <th>Status</th>
                        <th style="width: 20%">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($clearancetenants as $clearancetenant)
                        <tr>
                            <td>{{ $loop->iteration ?? '-' }}</td>
                            <td>{{ $clearancetenant->lease->tenant->thirdParty->TradingName ?? '-' }}</td>
                            <td>
                                {{ $clearancetenant->ExitDate ? Carbon::parse($clearancetenant->ExitDate)->format('d/m/Y') : '-' }}
                            </td>
                            <td>
                                @if($clearancetenant->FinalInspection)
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Yes</span>
                                @else
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> No</span>
                                @endif
                            </td>
                            <td>
                                @if($clearancetenant->AllDuesPaid)
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Yes</span>
                                @else
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> No</span>
                                @endif
                            </td>
                            <td>
                                @if($clearancetenant->KeysReturned)
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Yes</span>
                                @else
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> No</span>
                                @endif
                            </td>
                            <td>{{ $clearancetenant->code->Description ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $clearancetenant->Status->badgeColor() }}">
                                    {{ $clearancetenant->Status->label() }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="{{ route('tenantclearance.show', $clearancetenant->Id) }}"
                                       class="btn btn-sm btn-info text-white" title="View Clearance Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('tenantclearance.edit', $clearancetenant->Id) }}"
                                       class="btn btn-sm btn-warning" title="Edit Clearance">
                                        <i class="bi bi-pencil-square"></i>
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
            <i class="bi bi-info-circle me-2"></i> No clearance records registered yet.
        </div>
    @endif
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#tenantclearance').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection
