@extends('layouts.app')

@section('content')
<div class="container my-5">
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-bold text-dark">Prequalification Applications</h1>
    </div>

    <div class="border-0">
        <div class="card-body">
            @if ($applications->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                No applications Received.
            </div>
            @else
            <div class="table-responsive">
                <table id="applicationsTable" class="table table-striped table-hover align-middle w-100">
                    <thead class="table-primary">
                        <tr>
                            <th>Application ID</th>
                            <th>Round Name</th>
                            <th>Supplier Name</th>
                            <th>Email</th>
                            <th>Phone No</th>
                            <th>Categories</th>
                            <th>Application Date</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($applications as $application)
                        <tr>
                            <td><span class="fw-semibold text-dark">APP-{{ $application->ApplicationID }}</span></td>
                            <td><span class="fw-semibold">{{ $application->round->Title ?? 'N/A' }}</span></td>
                            <td>{{ $application->supplier->ThirdPartyName }}</td>
                            <td>{{ $application->supplier->Email ?? 'N/A' }}</td>
                            <td>{{ $application->supplier->Phone ?? 'N/A' }}</td>
                            <td>
                                @if($application->category)
                                <span class="badge bg-secondary">{{ $application->category->CategoryName }}</span>
                                @else
                                <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td data-order="{{ $application->SubmittedOn ? \Carbon\Carbon::parse($application->SubmittedOn)->format('Y-m-d H:i:s') : '' }}">
                                {{ $application->SubmittedOn ? \Carbon\Carbon::parse($application->SubmittedOn)->format('M d, Y') : '' }}
                            </td>
                            <td>
                                <span class="badge bg-{{ $application->Status->getColor() }} px-3 py-2">
                                    {{ $application->Status->getLabel() }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="{{ route('prequalification.applications.show', $application->ApplicationID) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    <form action="{{ route('prequalification.applications.destroy', $application->ApplicationID) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this application?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#applicationsTable').DataTable({
            responsive: true,
            pageLength: 10,
            lengthChange: false,
            ordering: true,
            autoWidth: false,
            order: [[6, 'desc']],
            columnDefs: [{
                orderable: false,
                targets: -1
            }],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search applications..."
            }
        });
    });
</script>
@endpush
@endsection