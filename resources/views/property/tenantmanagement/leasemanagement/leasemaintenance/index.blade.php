@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'Lease Agreements')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        /* Ensure action buttons stay on the same line and are neatly aligned */
        .action-buttons {
            display: flex;
            flex-wrap: nowrap;
            gap: 0.4rem;
            align-items: center;
        }

        .action-buttons form {
            margin: 0;
        }
    </style>
@endsection

@section('content')
<div class="container mt-4">

    <!-- Page Header -->
    <div class="d-flex justify-content-end align-items-center mb-3">
        <a href="{{ route('addlease.create') }}" class="btn btn-primary">
            <i class="bi bi-file-earmark-plus me-1"></i> New Lease
        </a>
    </div>

    <p class="text-muted">
        <small>This screen displays a list of all lease agreements — both active and inactive.</small>
    </p>

    @if($newleases->count())
        <div class="card shadow-sm">
            <div class="card-body">
                <table id="leaseagreement"
                       class="table table-bordered table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th style="width: 5%">#</th>
                        <th>Lease No.</th>
                        <th>Tenant</th>
                        <th>Property</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Frequency</th>
                        <th>Due Date</th>
                        <th style="width: 25%">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($newleases as $newlease)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $newlease->LeaseNumber ?? '-' }}</td>
                            <td>{{ $newlease->tenant->thirdParty->ThirdPartyName ?? '-' }}</td>
                            <td>{{ $newlease->property->PropertyName ?? '-' }}</td>
                            <td>{{ $newlease->StartDate ? Carbon::parse($newlease->StartDate)->format('d/m/Y') : '-' }}</td>
                            <td>{{ $newlease->EndDate ? Carbon::parse($newlease->EndDate)->format('d/m/Y') : '-' }}</td>
                            <td>{{ $newlease->code->Description ?? '-' }}</td>
                            <td>{{ $newlease->DueDay ?? '-' }}</td>
                            <td>
                                <div class="action-buttons">
                                    @if ($newlease->IsActive === false || $newlease->IsActive === 'Inactive')
                                        <button class="btn btn-sm btn-secondary" title="Inactive Lease">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    @else
                                        <a href="{{ route('addlease.show', $newlease->Id) }}"
                                           class="btn btn-sm btn-info text-white" title="View Lease">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endif

                                    @if($newlease->IsOfferGenerated)
                                        <button class="btn btn-sm btn-secondary" title="Offer Generated - Lease Locked" >
                                            <i class="bi bi-lock"></i>
                                        </button>
                                    @else
                                        <a href="{{ route('addlease.edit', $newlease->Id) }}"
                                           class="btn btn-sm btn-warning" title="Edit Lease">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    @endif

                                    @if($newlease->invoices()->exists())
                                        <button class="btn btn-sm btn-secondary" title="In Use">
                                            <i class="bi bi-lock"></i>
                                        </button>
                                    @else
                                        <form action="{{ route('addlease.destroy', $newlease->Id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Are you sure you want to delete this lease?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger" title="Delete Lease">
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
    @else
        <div class="alert alert-info mt-3">
            <i class="bi bi-info-circle me-2"></i> No lease agreements registered yet.
        </div>
    @endif

</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#leaseagreement').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection
