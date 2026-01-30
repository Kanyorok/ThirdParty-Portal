@extends('layouts.app')

@section('title', 'Supplier Details')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
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

    .status-pill.info {
        background-color: #0dcaf0;
        color: #212529;
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
            @php
            $supplierMaster = \App\Models\ThirdParty\SupplierMaster::where('ThirdPartyId', $supplier->Id)->with(['suppliers.category.itemCategories'])->first();
            $approvalStatus = $supplierMaster?->ApprovalStatus;
            @endphp
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
                        <td>{{ $supplier->businessType?->Description ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Registration Number</td>
                        <td>{{ $supplier->RegistrationNumber ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Prequalified</td>
                        <td>
                            @if ($supplierMaster?->IsPrequalified)
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
                            $statusClass = $approvalStatus?->getBadgeClass() ?? 'pending';
                            $statusLabel = $approvalStatus?->label() ?? 'Pending';
                            $statusIcon = match($statusLabel) {
                                'Approved' => 'fas fa-check-circle',
                                'Pending' => 'fas fa-clock',
                                'Rejected', 'Suspended' => 'fas fa-times-circle',
                                'Submitted' => 'fas fa-paper-plane',
                                default => 'fas fa-info-circle'
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
                        <td>{{ $supplier->country?->Name ?? 'N/A' }}</td>
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
                            @php
                            $prequalifiedCats = $supplierMaster?->suppliers->where('Active_Status', true)->unique('SupplierCategoryID') ?? collect();
                            @endphp

                            @if(!$supplierMaster?->IsPrequalified)
                            <div class="text-muted fst-italic">
                                You are not prequalified to supply any category.
                            </div>
                            @elseif($prequalifiedCats->isNotEmpty())
                            <div class="d-flex flex-column gap-2">
                                @foreach($prequalifiedCats as $supplierRow)
                                @php $category = $supplierRow->category; @endphp
                                @if($category)
                                <div class="border rounded p-2">
                                    <div class="fw-bold text-primary">{{ $category->CategoryName ?? $category->Description ?? 'Category' }}</div>
                                    @if($category->itemCategories && $category->itemCategories->isNotEmpty())
                                    <div class="small text-muted mt-1">
                                        Items: {{ $category->itemCategories->pluck('Name')->join(', ') }}
                                    </div>
                                    @else
                                    <div class="small text-muted mt-1 fst-italic">No specific items listed</div>
                                    @endif
                                </div>
                                @endif
                                @endforeach
                            </div>
                            @else
                            <div class="text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i> No categories assigned despite being prequalified.
                            </div>
                            @endif
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