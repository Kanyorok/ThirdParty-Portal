@extends('layouts.app')
@section('title', 'Assign Referrals')

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
                <i class="bi bi-person-check me-2"></i> Assign Insurance Referrals
            </h5>
        </div>

        <div class="card-body p-4">
            <p class="text-muted small mb-4">
                Below is a list of <strong>insurance referrals</strong> pending assignment. Use the dropdown to assign each referral to an officer.
            </p>

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="assignTable">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">#</th>
                            <th>Client Name</th>
                            <th>Product</th>
                            <th>Referral Date</th>
                            <th>Status</th>
                            <th>Assign To</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($referrals as $referral)
                        <tr>
                            <form method="POST" action="{{ route('bancassurance.referrals.assign', $referral->Id) }}">
                                @csrf
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $referral->ClientName ?? '-' }}</td>
                                <td>{{ $referral->insuranceProduct->Description ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($referral->ReferralDate)->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $referral->Status->Label() }}</span>
                                </td>
                                <td style="min-width:180px;">
                                    <select name="AssignedTo" class="form-select form-select-sm rounded-pill" required>
                                        <option value="">-- Select Officer --</option>
                                        @foreach($employees as $emp)
                                        <option value="{{ $emp->Id }}">{{ $emp->FirstName }} {{ $emp->LastName }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-center">
                                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">
                                        <i class="bi bi-check2-circle me-1"></i> Assign
                                    </button>
                                </td>
                            </form>
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
    $(document).ready(function() {
        $('#assignTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search referrals..."
            }
        });
    });
</script>
@endsection