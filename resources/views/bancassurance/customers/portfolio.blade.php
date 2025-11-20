@extends('layouts.app')

@section('title', 'Customer Portfolio')

@section('styles')
<!-- ✅ DataTables Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<style>
    /* 🌟 Header Styling */
    .portfolio-header {
        background: linear-gradient(90deg, #0d6efd, #0b5ed7);
        color: #fff;
        padding: 1rem 1.5rem;
        border-radius: 0.75rem 0.75rem 0 0;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    /* 👤 Customer Info */
    .customer-info dt {
        font-weight: 600;
        color: #495057;
    }
    .customer-info dd {
        margin-bottom: 10px;
        color: #212529;
    }

    /* 🪶 Cards */
    .card {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }
    .card-title {
        font-weight: 600;
        color: #0d6efd;
        display: flex;
        align-items: center;
    }
    .card-title i {
        color: #0d6efd;
    }

    /* 🧩 Table Styling */
    table.dataTable thead {
        background-color: #f8f9fa;
        color: #495057;
    }
    table.dataTable tbody tr:hover {
        background-color: #f9fbfd;
    }

    /* 🏷️ Policy Status Badge */
    .badge-status {
        padding: 0.35em 0.65em;
        font-size: 0.75rem;
        border-radius: 10rem;
        font-weight: 500;
    }
    .badge-active { background-color: #198754; color: #fff; }
    .badge-pending { background-color: #ffc107; color: #212529; }
    .badge-expired { background-color: #dc3545; color: #fff; }
    .badge-default { background-color: #6c757d; color: #fff; }

    /* 📱 Responsive Tweaks */
    @media (max-width: 768px) {
        .customer-info .col-md-4 {
            margin-bottom: 1rem;
        }
    }
</style>
@endsection

@section('content')
<div class="container mt-4">

    {{-- 🧾 Portfolio Header --}}
    <div class="portfolio-header mb-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-person-circle me-2"></i>
            Customer Portfolio
        </h5>
        <a href="{{ route('bancassurance.customers.index') }}" class="btn btn-light btn-sm rounded-pill">
            <i class="bi bi-arrow-left-circle me-1"></i> Back to List
        </a>
    </div>

    {{-- 👤 Customer Info --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">
                <i class="bi bi-person-fill me-2"></i>{{ $customer->thirdParty->ThirdPartyName ?? 'N/A' }}
            </h5>

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

    {{-- 📄 Policies --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-3">
                <i class="bi bi-file-earmark-text me-2"></i> Policies
            </h5>

            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0" id="portfolio">
                    <thead class="table-light text-center">
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
                            <tr class="text-center">
                                <td>{{ $policy->PolicyNumber ?? $policy->Id }}</td>
                                <td>{{ $policy->product->Name ?? 'N/A' }}</td>
                                <td>{{ $policy->insurer->Name ?? 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($policy->PolicyStartDate)->format('d/m/Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($policy->PolicyEndDate)->format('d/m/Y') }}</td>
                                <td>
                                    @php
                                        $statusLabel = $policy->Status->label() ?? 'N/A';
                                        $badgeClass = match(strtolower($statusLabel)) {
                                            'active' => 'badge-active',
                                            'pending' => 'badge-pending',
                                            'expired' => 'badge-expired',
                                            default => 'badge-default'
                                        };
                                    @endphp
                                    <span class="badge badge-status {{ $badgeClass }}">
                                        {{ ucfirst($statusLabel) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-4 d-block mb-2"></i>
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
<!-- ✅ jQuery + DataTables Bootstrap 5 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {
        $('#portfolio').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search policies..."
            }
        });
    });
</script>
@endsection
