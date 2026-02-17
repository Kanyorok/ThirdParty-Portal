@extends('layouts.app')
@section('title', 'Units Per Floor')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <style>
        /* Improve readability for table text */
        #propertyunits td {
            white-space: nowrap;
        }
        
        /* Ensure table scrolls horizontally when needed */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Prevent card body from adding extra padding that affects scroll */
        .card-body {
            padding: 0;
        }
        
        /* Add padding back to table */
        #propertyunits {
            margin: 1rem;
            width: calc(100% - 2rem);
        }
    </style>
@endsection

@section('content')
<div class="container mt-4">


    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <small>This screen displays all registered property units per floor.</small>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('addunit.bulkCreate') }}" class="btn btn-outline-primary shadow-sm">
                <i class="bi bi-upload me-1"></i> Bulk Upload Units
            </a>

            <a href="{{ route('addunit.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Add Unit
            </a>
        </div>
    </div>



    @if($units->count())
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
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
                                <td>{{ $unit->property->PropertyName ?? '-' }}</td>
                                <td>{{ $unit->blocks?->BlockName ?? 'N/A' }}</td>
                                <td>{{ $unit->floors?->FloorLabel ?? 'N/A' }}</td>
                                <td>{{ $unit->UnitCode ?? '-' }}</td>
                                <td>{{ $unit->UnitSize ?? '-' }}</td>

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

                                <td>{{ $unit->Remarks ?? '-' }}</td>

                                <td>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="{{ route('addunit.edit', $unit->Id) }}"
                                           class="btn btn-sm btn-warning"
                                           title="Edit Unit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        @if($unit->unitlease()->exists())
                                            <button class="btn btn-sm btn-secondary" title="Unit in Use">
                                                <i class="bi bi-lock"></i>
                                            </button>
                                        @else
                                            <form action="{{ route('addunit.destroy', $unit->Id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Are you sure you want to delete this unit?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete Unit">
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
            <i class="bi bi-info-circle me-2"></i> No property units registered yet.
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
            lengthChange: true,
            scrollX: true,
            autoWidth: false
        });
    });
</script>
@endsection
