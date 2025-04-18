@extends('layouts.app')

@section('title', 'SASRA Auditor List')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>SASRA Auditor List</h3>
        <div>
            <a href="{{ route('sasra-auditors.download') }}" class="btn btn-outline-success">Download List</a>
            <a href="{{ route('sasra-auditors.import') }}" class="btn btn-primary">Upload New List</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($auditors->isEmpty())
        <div class="alert alert-info">
            No auditors found. Please upload a list.
        </div>
    @else
    <div class="table-responsive dt-responsive">
            <table id="auditorsTable" class="table table-striped table-bordered nowrap">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Firm Name</th>
                        <th>Physical Address</th>
                        <th>Postal Address</th>
                        <th>Town</th>
                        <th>Status</th>
                        <th>Date Uploaded</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($auditors as $index => $auditor)
                        <tr>
                            <td>{{ ($auditors->currentPage() - 1) * $auditors->perPage() + $index + 1 }}</td>
                            <td>{{ $auditor->FirmName }}</td>
                            <td>{{ $auditor->PhysicalAddress }}</td>
                            <td>{{ $auditor->PostalAddress }}</td>
                            <td>{{ $auditor->Town }}</td>
                            <td>{{ $auditor->Status }}</td>
                            <td>{{ \Carbon\Carbon::parse($auditor->created_at)->format('d M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th>Firm Name</th>
                        <th>Physical Address</th>
                        <th>Postal Address</th>
                        <th>Town</th>
                        <th>Status</th>
                        <th>Date Uploaded</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Pagination Links -->
        <div class="d-flex justify-content-center">
            {{ $auditors->links() }}
        </div>
    @endif
</div>

<!-- Include DataTables JS -->
@push('scripts')
<script>
    $(document).ready(function() {
        $('#auditorsTable').DataTable({
            paging: true, // Disable DataTables pagination since Laravel handles it
            searching: true,
            ordering: true,
            info: false
        });
    });
</script>
@endpush
@endsection