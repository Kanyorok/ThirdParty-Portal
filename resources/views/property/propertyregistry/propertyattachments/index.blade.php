@extends('layouts.app')
@section('title', 'Property Documents')

@section('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <style>
        .table th,
        .table td {
            vertical-align: middle !important;
            white-space: nowrap; /* 🔥 prevents text wrapping */
        }

        /* Keep action buttons tidy */
        /* .btn {
            margin: 2px;
        } */

        /* Ensure proper DataTables width */
        div.dataTables_wrapper {
            width: 100%;
        }
    </style>
@endsection

@section('content')
<div class="container mt-4">

    <!-- Page Header -->
    <div class="d-flex justify-content-end align-items-center mb-3">
        <a href="{{ route('attachments.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-paperclip me-1"></i> Attach Document
        </a>
    </div>

    <p class="text-muted">
        <small>This screen displays a list of all property-related attachments.</small>
    </p>

    @if($propertyattachments->count())
        <div class="card shadow-sm border-0">
            <div class="card-body">

                <!-- 🔥 Horizontal scroll wrapper -->
                <div class="table-responsive">
                    <table id="propertyattachment"
                           class="table table-bordered table-striped table-hover align-middle mb-0 w-100">

                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%">#</th>
                                <th>Property</th>
                                <th>Document Title</th>
                                <th style="width: 5%">Type</th>
                                <th>Description</th>
                                <th class="text-center" style="width: 10%">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                        @foreach($propertyattachments as $propertyattachment)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $propertyattachment->property->PropertyName ?? '-' }}</td>
                                <td>{{ $propertyattachment->DocumentTitle ?? '-' }}</td>
                                <td>{{ $propertyattachment->documenttype->Description ?? '-' }}</td>
                                <td>{{ $propertyattachment->Description ?? '-' }}</td>

                                <td class="text-center">
                                    <div class="d-inline-flex flex-wrap gap-1">

                                        <a href="{{ route('attachments.show', $propertyattachment->Id) }}"
                                           class="btn btn-sm btn-info"
                                           title="View Attachment">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a href="{{ route('attachments.edit', $propertyattachment->Id) }}"
                                           class="btn btn-sm btn-warning"
                                           title="Edit Attachment">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <form action="{{ route('attachments.destroy', $propertyattachment->Id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Are you sure you want to delete this property attachment?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-sm btn-danger"
                                                    title="Delete Attachment">
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

            </div>
        </div>
    @else
        <div class="alert alert-info mt-3">
            <i class="bi bi-info-circle me-2"></i> No property attachments registered yet.
        </div>
    @endif

</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#propertyattachment').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            scrollX: true,        // ✅ horizontal scrolling
            autoWidth: false,    // ✅ prevents column misalignment
            responsive: false   // ✅ important when using scrollX
        });
    });
</script>
@endsection
