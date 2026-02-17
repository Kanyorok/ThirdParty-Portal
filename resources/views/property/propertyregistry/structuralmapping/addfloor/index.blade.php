@extends('layouts.app')

@section('title', 'Floors per Block')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <style>
        .floors-table-wrapper {
            width: 100%;
            max-height: 65vh;
            overflow: auto;
        }

        table {
            min-width: 900px;
        }

        th, td {
            white-space: nowrap;
            vertical-align: middle !important;
        }

        .action-buttons {
            display: flex;
            gap: 0.4rem;
            align-items: center;
        }
    </style>
@endsection

@section('content')
<div class="container mt-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-muted mb-0">
            <small>This screen displays a list of all registered floors in property blocks.</small>
        </p>

        <div class="d-flex gap-2">
            <a href="{{ route('addfloor.bulkCreate') }}" class="btn btn-outline-primary shadow-sm">
                <i class="bi bi-upload me-1"></i> Bulk Upload Floors
            </a>

            <a href="{{ route('addfloor.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Add Floor
            </a>
        </div>
    </div>

    @if($floors->count())
        <div class="card shadow-sm">
            <div class="card-body p-2">
                <div class="floors-table-wrapper">
                    <table id="PropertyFloorsTable"
                           class="table table-bordered table-striped table-hover align-middle mb-0 w-100">

                        <thead class="table-light">
                            <tr>
                                <th style="width:5%">#</th>
                                <th>Property</th>
                                <th>Block</th>
                                <th>Floor Name</th>
                                <th>Notes</th>
                                <th style="width:15%">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($floors as $floor)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $floor->property->PropertyName ?? '-' }}</td>
                                    <td>{{ $floor->block->BlockName ?? '-' }}</td>
                                    <td class="fw-semibold">{{ $floor->FloorLabel ?? '-' }}</td>
                                    <td>{{ $floor->FloorNotes ?? '-' }}</td>

                                    <td>
                                        <div class="action-buttons">

                                            <a href="{{ route('addfloor.edit', $floor->Id) }}"
                                               class="btn btn-sm btn-warning"
                                               title="Edit Floor">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            @if($floor->units()->exists())
                                                <button class="btn btn-sm btn-secondary" title="Floor in Use">
                                                    <i class="bi bi-lock"></i>
                                                </button>
                                            @else
                                                <form action="{{ route('addfloor.destroy', $floor->Id) }}"
                                                      method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this floor?');">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button class="btn btn-sm btn-danger" title="Delete Floor">
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
            <i class="bi bi-info-circle me-2"></i> No property floors registered yet.
        </div>
    @endif

</div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#PropertyFloorsTable').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection
