@extends('layouts.app')
@section('title', 'Referral Performance')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 20px;
        padding: 4px 12px;
        border: 1px solid #ced4da;
    }
    .dataTables_wrapper .dataTables_length select {
        border-radius: 20px;
        padding: 3px 10px;
        border: 1px solid #ced4da;
    }
    table.dataTable tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>
@endsection

@section('content')
<div class="container my-4" style="max-width: 1000px;">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-primary text-white py-3 rounded-top-4">
            <h5 class="mb-0 fw-normal">
                <i class="bi bi-graph-up-arrow me-2"></i> Staff & Branch Referral Performance
            </h5>
        </div>

        <div class="card-body p-4">
            <p class="text-muted small mb-4">
                This report summarizes the performance of staff and branches based on referral outcomes.
            </p>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="performanceTable">
                    <thead class="table-light">
                        <tr>
                            <th>Staff</th>
                            <th>Branch</th>
                            <th class="text-center">Total Referrals</th>
                            <th class="text-center text-success">Converted</th>
                            <th class="text-center text-warning">Pending</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($performance as $entry)
                            <tr>
                                <td>{{ $entry['StaffName'] }}</td>
                                <td>{{ $entry['BranchName'] }}</td>
                                <td class="text-center fw-semibold">{{ $entry['Total'] }}</td>
                                <td class="text-center text-success fw-semibold">{{ $entry['Converted'] }}</td>
                                <td class="text-center text-warning fw-semibold">{{ $entry['Pending'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-light text-end rounded-bottom-4 py-3 px-4">
            <a href="{{ route('bancassurance.referrals.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-arrow-left-circle me-2"></i> Back to Referrals
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#performanceTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search performance..."
            }
        });
    });
</script>
@endsection
