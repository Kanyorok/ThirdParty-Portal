@extends('layouts.app')
@section('title', 'Property Type')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container mt-4">

    <!-- Page Header -->
    <div class="d-flex justify-content-end align-items-center mb-3">
        <a href="{{ route('propertytype.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add Type
        </a>
    </div>

    <p class="text-muted">
        <small>This is a list of property types linked to specific categories.</small>
    </p>

    @if($types->count())
        <div class="card shadow-sm">
            <div class="card-body">
                <table id="propertytype" class="table table-bordered table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th style="width: 5%">#</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th style="width: 20%">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($types as $Index => $type)
                        <tr>
                            <td>{{ $Index + 1 }}</td>
                            <td>{{ $type->PropertyTypeName ?? '-' }}</td>
                            <td>{{ $type->propertycategory->Name ?? '-' }}</td>
                            <td>{{ $type->Description ?? '-' }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('propertytype.edit', $type->Id) }}"
                                       class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>

                                    @if($type->property()->exists())
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="bi bi-lock"></i> In Use
                                        </button>
                                    @else
                                        <form action="{{ route('propertytype.destroy', $type->Id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Are you sure you want to delete this type?');">
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
            No property type registered yet.
        </div>
    @endif
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#propertytype').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection
