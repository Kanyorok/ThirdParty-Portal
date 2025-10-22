@extends('layouts.app')
@section('title', 'Units Per Floor')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <style>
        /* Force table text to wrap instead of overflowing */
        #propertyunits td {
            white-space: normal !important;
            word-wrap: break-word;
            max-width: 200px; /* optional: limit width so wrap actually happens */
        }
    </style>
@endsection

@section('content')
<div class="container mt-4">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('addunit.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add Unit
        </a>
    </div>

    <p class="text-muted">
        <small>The list below shows different units per floor.</small>
    </p>

    @if($units->count())
        <div class="card shadow-sm">
            <div class="card-body">
                <table id="propertyunits" class="table table-bordered table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th style="width: 5%">#</th>
                        <th>Property</th>
                        <th>Block</th>
                        <th>Floor</th>
                        <th>Unit Code</th>
                        <th>Size (sq.ft)</th>
                        <th>Rentable?</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th style="width: 20%">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($units as $unit)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $unit->property->PropertyName ?? '-'}}</td>
                            <td>{{ $unit->blocks?->BlockName ?? 'N/A' }}</td>
                            <td>{{ $unit->floors?->FloorLabel ?? 'N/A' }}</td>
                            <td>{{ $unit->UnitCode ?? '-'}}</td>
                            <td>{{ $unit->UnitSize ?? '-'}}</td>
                            <td>
                                    <span class="badge bg-{{ $unit->IsRentable ? 'success' : 'secondary' }}">
                                        {{ $unit->IsRentable ? 'Yes' : 'No' }}
                                    </span>
                            </td>
                            <td>
                                    <span class="badge bg-{{ $unit->CurrentStatus ? 'success' : 'danger' }}">
                                        {{ $unit->CurrentStatus ? 'Vacant' : 'Occupied' }}
                                    </span>
                            </td>
                            <td>{{ $unit->Remarks ?? '-'}}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('addunit.edit', $unit->Id) }}"
                                       class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>

                                    @if($unit->unitlease()->exists())
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="bi bi-lock"></i> In Use
                                        </button>
                                    @else
                                        <form action="{{ route('addunit.destroy', $unit->Id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Are you sure you want to delete this unit?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger">
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
            <i class="bi bi-info-circle me-2"></i> No property unit registered yet.
        </div>
    @endif
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#propertyunits').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection
