@extends('layouts.app')

@section('title', 'Supplier Details')

@section('styles')
<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
    /* Custom styles for the status pills to match the table view */
    .status-pill {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 999px;
        font-weight: 600;
        color: white;
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

    .info-label {
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 0.25rem;
        display: block;
    }

    .info-value {
        font-size: 1.1rem;
        word-break: break-word;
    }

    .nav-tabs .nav-link {
        font-weight: 600;
        color: #6c757d;
    }

    .nav-tabs .nav-link.active {
        color: #0d6efd;
        border-color: #dee2e6 #dee2e6 #fff;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0 text-primary">Supplier Details</h3>
        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary d-flex align-items-center">
            <i class="fas fa-arrow-left me-2"></i> Back to List
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <ul class="nav nav-tabs mb-4" id="supplierTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">
                        <i class="fas fa-info-circle me-2"></i>General
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false">
                        <i class="fas fa-address-book me-2"></i>Contact
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tax-tab" data-bs-toggle="tab" data-bs-target="#tax" type="button" role="tab" aria-controls="tax" aria-selected="false">
                        <i class="fas fa-file-invoice-dollar me-2"></i>Tax & Compliance
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="supplierTabsContent">
                {{-- General Tab --}}
                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <span class="info-label">Legal Name:</span>
                            <p class="info-value">{{ $supplier->ThirdPartyName }}</p>
                        </div>
                        <div class="col-md-6">
                            <span class="info-label">Trading Name:</span>
                            <p class="info-value">{{ $supplier->TradingName ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <span class="info-label">Business Type:</span>
                            <p class="info-value">{{ $supplier->BusinessType->label() ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <span class="info-label">Registration Number:</span>
                            <p class="info-value">{{ $supplier->RegistrationNumber ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <span class="info-label">Prequalified:</span>
                            <p class="info-value">{{ $supplier->IsPrequalified ? 'Yes' : 'No' }}</p>
                        </div>
                        <div class="col-md-6">
                            <span class="info-label">Approval Status:</span>
                            <p class="info-value">
                                <span class="status-pill {{ $supplier->ApprovalStatus->getBadgeClass() }}">
                                    {{ $supplier->ApprovalStatus->label() }}
                                </span>
                            </p>
                        </div>
                        <div class="col-12">
                            <span class="info-label">Categories:</span>
                            <p class="info-value">
                                @forelse($supplier->supplierCategories as $category)
                                <span class="badge bg-secondary me-1">{{ $category->CategoryName }}</span>
                                @empty
                                N/A
                                @endforelse
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Contact Tab --}}
                <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <span class="info-label">Email:</span>
                            <p class="info-value">{{ $supplier->Email ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <span class="info-label">Phone:</span>
                            <p class="info-value">{{ $supplier->Phone ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <span class="info-label">Website:</span>
                            <p class="info-value">{{ $supplier->Website ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <span class="info-label">Physical Address:</span>
                            <p class="info-value">{{ $supplier->PhysicalAddress ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <span class="info-label">Country:</span>
                            <p class="info-value">{{ $supplier->Country ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Tax & Compliance Tab --}}
                <div class="tab-pane fade" id="tax" role="tabpanel" aria-labelledby="tax-tab">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <span class="info-label">Tax PIN:</span>
                            <p class="info-value">{{ $supplier->TaxPIN ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <span class="info-label">VAT Number:</span>
                            <p class="info-value">{{ $supplier->VATNumber ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-5">

            <div class="d-flex justify-content-end">
                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning me-2">Edit Supplier</a>
            </div>
        </div>
    </div>
</div>
@endsection