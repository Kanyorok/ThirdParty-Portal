@extends('layouts.app')
@section('title', 'Property Management')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<style>
    .table th, .table td {
        vertical-align: middle !important;
    }
    .action-btns .btn {
        margin: 2px;
    }
    .page-title {
        font-weight: 600;
        font-size: 1.45rem;
    }
    .subtitle {
        font-size: .85rem;
        color: #6c757d;
        margin-top: -5px;
    }
</style>
@endsection

@section('content')
<div class="container mt-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <small>This screen displays a list of all registered properties.</small>
        </div>

        <a href="{{ route('PropertyRegistry.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Add Property
        </a>
    </div>

    @if($properties->count())
        <div class="card shadow-sm border-0">
            <div class="card-body">

                <table id="propertyregistry" class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Property Name</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Country</th>
                            <th>Town/City</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
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
                                <span class="badge {{ $property->IsActive ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $property->IsActive ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td class="text-center">
                                <div class="action-btns d-inline-flex">
                                    <!-- View -->
                                    <a href="{{ route('PropertyRegistry.show', $property->Id) }}"
                                       class="btn btn-sm btn-info text-white" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <!-- Edit -->
                                    <a href="{{ route('PropertyRegistry.edit', $property->Id) }}"
                                       class="btn btn-sm btn-warning text-white" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <!-- Delete / In Use -->
                                    @if($property->getBlockByProperty()->exists())
                                        <button class="btn btn-sm btn-secondary" title="In Use">
                                            <i class="bi bi-lock"></i>
                                        </button>
                                    @else
                                        <form action="{{ route('PropertyRegistry.destroy', $property->Id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Are you sure you want to delete this property?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger" title="Delete">
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
