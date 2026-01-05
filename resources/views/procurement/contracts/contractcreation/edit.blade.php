@extends('layouts.app')
@section('title', 'Edit Contract')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4>✏️ Edit Contract Draft</h4>
                        @if($award)
                            <p class="text-muted mb-0">
                                Editing contract for: <strong>{{ $award->tender->TenderNo ?? 'N/A' }}</strong>
                            </p>
                        @endif
                    </div>
                    <a href="{{ route('contracts.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Contracts
                    </a>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($award)
                    <!-- Award Information Card -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">🏆 Award Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td><strong>Tender Reference:</strong></td>
                                            <td>{{ $award->tender->TenderNo ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tender Title:</strong></td>
                                            <td>{{ $award->tender->Title ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Winning Supplier:</strong></td>
                                            <td>{{ 
                                                $award->winningSupplier->supplierMaster->party->TradingName 
                                                ?? $award->winningSupplier->thirdParty->TradingName 
                                                ?? $award->winningSupplier->thirdParty->Name 
                                                ?? $award->winningSupplier->SupplierName 
                                                ?? 'N/A' 
                                            }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td><strong>Contract Reference:</strong></td>
                                            <td>{{ $award->ContractRef ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Award Date:</strong></td>
                                            <td>{{ $award->AwardDate ? $award->AwardDate->format('Y-m-d') : 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contract Edit Form -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">📋 Contract Details</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('contracts.update', $award->Id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="award_type" value="{{ $type ?? 'tender' }}">

                                <!-- Contract Basic Information -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Contract Value <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" name="contract_value" class="form-control"
                                                   value="{{ old('contract_value', $award->ContractValue ?? $award->AwardedAmount ?? '') }}"
                                                   step="0.01" min="0" placeholder="0.00">
                                            <span class="input-group-text">{{ is_object($award->tender->Currency) ? $award->tender->Currency->Code : ($award->tender->Currency ?? 'KES') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contract Duration -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Contract Start Date <span class="text-danger">*</span></label>
                                        <input type="date" name="start_date" class="form-control"
                                               value="{{ old('start_date', $award->ContractStartDate ? $award->ContractStartDate->format('Y-m-d') : '') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Contract End Date <span class="text-danger">*</span></label>
                                        <input type="date" name="end_date" class="form-control"
                                               value="{{ old('end_date', $award->ContractEndDate ? $award->ContractEndDate->format('Y-m-d') : '') }}">
                                    </div>
                                </div>

                                <!-- Terms and Conditions -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Payment Terms <span class="text-danger">*</span></label>
                                        <textarea name="payment_terms" class="form-control" rows="3"
                                                  placeholder="Specify payment schedule, milestones, and conditions">{{ old('payment_terms', $award->PaymentTerms ?? '') }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Delivery Terms</label>
                                        <textarea name="delivery_terms" class="form-control" rows="3"
                                                  placeholder="Specify delivery timeline, locations, and acceptance criteria">{{ old('delivery_terms', $award->DeliveryTerms ?? '') }}</textarea>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Special Conditions</label>
                                    <textarea name="special_conditions" class="form-control" rows="3"
                                              placeholder="Any special conditions, penalties, or additional requirements">{{ old('special_conditions', $award->SpecialConditions ?? '') }}</textarea>
                                </div>

                                <!-- Action Buttons -->
                                <div class="row">
                                    <div class="col-md-12 text-end">
                                        <a href="{{ route('contracts.show', ['id' => $award->Id, 'type' => $type ?? 'tender']) }}" class="btn btn-outline-secondary me-2">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update Contract
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">
                        Award not found.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
