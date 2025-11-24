@extends('layouts.app')

@section('title', 'Supplier Details')

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.8rem;
            font-size: 0.85rem;
            border-radius: 999px;
            font-weight: 600;
            color: white;
            gap: 0.5rem;
        }

        .status-pill.approved {
            background-color: #198754;
        }

        .status-pill.pending {
            background-color: #ffc107;
            color: #212529;
        }

        .status-pill.rejected {
            background-color: #dc3545;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .table td,
        .table th {
            padding: 0.75rem;
        }

        .table-striped > tbody > tr:nth-of-type(odd) > * {
            background-color: #f8f9fa;
        }
    </style>
@endsection

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0 text-primary">Supplier Details</h3>
            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary d-flex align-items-center rounded-pill">
                <i class="fas fa-arrow-left me-2"></i> Back to List
            </a>
        </div>

        <div class="card p-4">
            <div class="table-responsive">
                <table class="table table-striped table-borderless">
                    <tbody>
                    <tr>
                        <td class="fw-bold">Legal Name</td>
                        <td>{{ $supplier->ThirdPartyName }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Trading Name</td>
                        <td>{{ $supplier->TradingName ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Business Type</td>
                        <td>{{ $supplier->BusinessType?->label() ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Registration Number</td>
                        <td>{{ $supplier->RegistrationNumber ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Prequalified</td>
                        <td>
                            @if ($supplier->IsPrequalified)
                            <span class="badge bg-success">Yes</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Approval Status</td>
                        <td>
                            @php
                                $statusClass = $supplier->ApprovalStatus?->getBadgeClass() ?? 'pending';
                                $statusLabel = $supplier->ApprovalStatus?->label() ?? 'Pending';
                                $statusIcon = match($statusLabel) {
                                'Approved' => 'fas fa-check-circle',
                                'Pending' => 'fas fa-clock',
                                'Rejected' => 'fas fa-times-circle',
                                default => ''
                                };
                            @endphp
                            <span class="status-pill {{ $statusClass }}">
                                <i class="{{ $statusIcon }}"></i> {{ $statusLabel }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Email</td>
                        <td>{{ $supplier->Email ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Phone</td>
                        <td>{{ $supplier->Phone ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Website</td>
                        <td>{{ $supplier->Website ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Physical Address</td>
                        <td>{{ $supplier->PhysicalAddress ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Country</td>
                        <td>{{ $supplier->Country ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Tax PIN</td>
                        <td>{{ $supplier->TaxPIN ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">VAT Number</td>
                        <td>{{ $supplier->VATNumber ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Categories</td>
                        <td>
                            @forelse($supplier->supplierCategories ?? [] as $category)
                                <span class="badge bg-secondary me-1">{{ $category->CategoryName }}</span>
                            @empty
                                N/A
                            @endforelse
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning me-2 rounded-pill">
                    <i class="fas fa-edit me-1"></i> Edit Supplier
                </a>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{-- No scripts needed for this clean, static view --}}
@endsection
