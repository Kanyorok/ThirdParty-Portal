@extends('layouts.app')

@section('title', 'Customer Portfolio')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<style>
    .portfolio-header {
        background: linear-gradient(90deg, #0d6efd, #0b5ed7);
        color: #fff;
        padding: 0.9rem 1.25rem;
        border-radius: 0.6rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 3px 10px rgba(13,110,253,.15);
    }

    /* 👤 Customer strip */
    .customer-strip {
        background: #fff;
        padding: 1rem 1.25rem;
        border-radius: 0.6rem;
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        align-items: center;
        box-shadow: 0 2px 8px rgba(0,0,0,.05);
    }

    .customer-strip .name {
        font-weight: 600;
        font-size: 1.05rem;
        color: #0d6efd;
    }

    .info-pill {
        background: #f8f9fa;
        padding: 0.45rem 0.75rem;
        border-radius: 999px;
        font-size: 0.85rem;
        color: #495057;
        display: flex;
        gap: .35rem;
        align-items: center;
    }

    /* 📄 Policies */
    .section-title {
        font-weight: 600;
        font-size: .9rem;
        color: #495057;
        margin-bottom: .5rem;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    table.dataTable {
        border-collapse: separate;
        border-spacing: 0 6px;
    }

    table.dataTable tbody tr {
        background: #fff;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
        border-radius: .4rem;
    }

    table.dataTable tbody td {
        border: none;
        vertical-align: middle;
        font-size: .85rem;
    }

    table.dataTable thead th {
        border: none;
        font-size: .75rem;
        text-transform: uppercase;
        color: #6c757d;
    }

    /* 🏷 Status */
    .badge-status {
        font-size: .7rem;
        padding: .35em .6em;
        border-radius: 999px;
        font-weight: 500;
    }
    .badge-active { background: #198754; color: #fff; }
    .badge-pending { background: #ffc107; color: #212529; }
    .badge-expired { background: #dc3545; color: #fff; }
    .badge-default { background: #6c757d; color: #fff; }
</style>
@endsection

@section('content')
<div class="container mt-4">

    {{-- Header --}}
    <div class="portfolio-header mb-3">
        <span>
            <i class="bi bi-person-circle me-2"></i>
            Customer Portfolio
        </span>
        <a href="{{ route('bancassurance.customers.index') }}"
           class="btn btn-light btn-sm rounded-pill">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    {{-- Customer Info --}}
    <div class="customer-strip mb-4">
        <span class="name">
            <i class="bi bi-person-fill me-1"></i>
            {{ $customer->thirdParty->ThirdPartyName ?? 'N/A' }}
        </span>

        <span class="info-pill">
            <strong>ID:</strong> {{ $customer->thirdParty->NationalID ?? 'N/A' }}
        </span>

        <span class="info-pill">
            <strong>Phone:</strong> {{ $customer->thirdParty->Phone ?? 'N/A' }}
        </span>

        <span class="info-pill">
            <strong>Email:</strong> {{ $customer->thirdParty->Email ?? 'N/A' }}
        </span>
    </div>

    {{-- Policies --}}
    <div class="section-title">Policies</div>

    <div class="table-responsive">
        <table class="table table-hover align-middle" id="portfolio">
            <thead>
                <tr class="text-center">
                    <th>Policy #</th>
                    <th>Product</th>
                    <th>Insurer</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            @forelse($policies as $policy)
                <tr class="text-center">
                    <td>{{ $policy->PolicyNumber ?? $policy->Id }}</td>
                    <td>{{ $policy->product->Name ?? 'N/A' }}</td>
                    <td>{{ $policy->insurer->Name ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($policy->PolicyStartDate)->format('d M Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($policy->PolicyEndDate)->format('d M Y') }}</td>
                    <td>
                        @php
                            $status = strtolower($policy->Status->label() ?? '');
                            $badge = match($status) {
                                'active' => 'badge-active',
                                'pending' => 'badge-pending',
                                'expired' => 'badge-expired',
                                default => 'badge-default'
                            };
                        @endphp
                        <span class="badge badge-status {{ $badge }}">
                            {{ ucfirst($status ?: 'N/A') }}
                        </span>
                    </td>
                </tr>
            @empty
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(function () {
        $('#portfolio').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            language: {
                searchPlaceholder: "Search policies…",
                search: ""
            }
        });
    });
</script>
@endsection
