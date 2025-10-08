@extends('layouts.app')

@section('title', 'Customer Portfolio')

@section('styles')
    <!-- DataTables Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .portfolio-header {
            background: #0d6efd;
            color: #fff;
            padding: 15px 25px;
            border-radius: 0.5rem 0.5rem 0 0;
        }
        .customer-info dt {
            font-weight: 600;
            color: #495057;
        }
        .customer-info dd {
            margin-bottom: 10px;
        }
        .card {
            padding: 1rem 1.5rem;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid mt-4">

    <!-- Customer Info -->
    <div class="card shadow-sm mb-4 border-0 rounded-3">
        <div class="card-body">
            <h5 class="card-title mb-3">{{ $customer->thirdParty->ThirdPartyName ?? 'N/A' }}</h5>
            <div class="row customer-info">
                <div class="col-md-4">
                    <dt>National ID</dt>
                    <dd>{{ $customer->thirdParty->NationalID ?? 'N/A' }}</dd>
                </div>
                <div class="col-md-4">
                    <dt>Phone</dt>
                    <dd>{{ $customer->thirdParty->Phone ?? 'N/A' }}</dd>
                </div>
                <div class="col-md-4">
                    <dt>Email</dt>
                    <dd>{{ $customer->thirdParty->Email ?? 'N/A' }}</dd>
                </div>
            </div>
        </div>
    </div>

    <!-- Policies -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">
            <h5 class="card-title mb-3">
                <i class="bi bi-file-earmark-text me-2"></i> Policies
            </h5>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle" id="portfolio">
                    <thead class="table-dark">
                        <tr>
                            <th>Policy Number</th>
                            <th>Product</th>
                            <th>Insurer</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($policies as $policy)
                            <tr>
                                <td>{{ $policy->PolicyNumber ?? $policy->Id }}</td>
                                <td>{{ $policy->product->Name ?? 'N/A' }}</td>
                                <td>{{ $policy->insurer->Name ?? 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($policy->PolicyStartDate)->format('d/m/Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($policy->PolicyEndDate)->format('d/m/Y') }}</td>
                                <td>{{ $policy->Status->label() ?? 'N/A' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">
                                    No policies found for this customer.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <!-- jQuery + DataTables Bootstrap 5 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#portfolio').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection
