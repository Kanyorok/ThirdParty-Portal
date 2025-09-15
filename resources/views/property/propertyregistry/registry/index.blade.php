@extends('layouts.app')
@section('title', 'Property Management')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container mt-4">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('PropertyRegistry.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add Property
        </a>
    </div>

    <p class="text-muted">
        <small>This screen displays a list of all registered properties.</small>
    </p>

    @if($properties->count())
        <div class="card shadow-sm">
            <div class="card-body">
                <table id="propertyregistry" class="table table-bordered table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%">#</th>
                            <th>Property Name</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Country</th>
                            <th>Town/City</th>
                            <th>Status</th>
                            <th style="width: 25%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($properties as $property)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $property->PropertyName ?? '-' }}</td>
                                <td>{{ $property->type->PropertyTypeName ?? '-' }}</td>
                                <td>{{ $property->propertyCategory->Name ?? '-' }}</td>
                                <td>{{ $property->propertyCountry->Name ?? '-' }}</td>
                                <td>{{ $property->propertyLocality->Name ?? '-' }}</td>
                                <td>
                                    @if($property->IsActive)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="{{ route('PropertyRegistry.show', $property->Id) }}" 
                                           class="btn btn-sm btn-info text-white">
                                           <i class="bi bi-eye"></i> View
                                        </a>
                                        <a href="{{ route('PropertyRegistry.edit', $property->Id) }}" 
                                           class="btn btn-sm btn-warning">
                                           <i class="bi bi-pencil-square"></i> Edit
                                        </a>

                                        @if($property->getBlockByProperty()->exists())
                                            <button class="btn btn-sm btn-secondary" disabled>
                                                <i class="bi bi-lock"></i> In Use
                                            </button>
                                        @else
                                            <form action="{{ route('PropertyRegistry.destroy', $property->Id) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Are you sure you want to delete this property?');">
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
            <i class="bi bi-info-circle me-2"></i> No properties registered yet.
        </div>
    @endif
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#propertyregistry').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection
