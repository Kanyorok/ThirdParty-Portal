@extends('layouts.app')
@section('title', 'Floors per Block')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container mt-4">

    <!-- Page Header -->
    <div class="d-flex justify-content-end align-items-center mb-3">
        <a href="{{ route('addfloor.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add Floor
        </a>
    </div>

    <p class="text-muted">
        <small>This screen displays a list of all registered floors in property blocks.</small>
    </p>

    @if($floors->count())
        <div class="card shadow-sm">
            <div class="card-body">
                <table id="propertyfloors" class="table table-bordered table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%">#</th>
                            <th>Property</th>
                            <th>Block</th>
                            <th>Floor Name</th>
                            <th>Notes</th>
                            <th style="width: 20%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($floors as $floor)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $floor->property->PropertyName ?? '-' }}</td>
                                <td>{{ $floor->block->BlockName ?? '-' }}</td>
                                <td>{{ $floor->FloorLabel ?? '-' }}</td>
                                <td>{{ $floor->FloorNotes ?? '-' }}</td>
                                <td>
                                    <div class="d-flex gap-2 flex-wrap">
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
                                                  onsubmit="return confirm('Are you sure you want to delete this floor?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete Floor">
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
            <i class="bi bi-info-circle me-2"></i> No property floors registered yet.
        </div>
    @endif
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#propertyfloors').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection
