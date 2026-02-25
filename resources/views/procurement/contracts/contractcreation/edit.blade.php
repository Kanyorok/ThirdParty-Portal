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

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Contract Tax Rule</label>
                                        <select name="contract_tax_id" class="form-select">
                                            <option value="">-- No tax --</option>
                                            @foreach(($taxRules ?? []) as $taxRule)
                                                <option value="{{ $taxRule->Id }}"
                                                    @selected((string) old('contract_tax_id', $award->ContractTaxID ?? '') === (string) $taxRule->Id)>
                                                    {{ $taxRule->taxType->TaxTypeName ?? 'Tax' }} ({{ number_format((float) $taxRule->Rate, 2) }}%)
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Used as default tax rule for contract invoices.</small>
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
                        <label for="terms" class="form-label fw-semibold">Payment Terms <span class="text-danger">*</span></label>
                        <select id="terms" name="payment_terms" required class="form-select @error('payment_terms') is-invalid @enderror">
                            <option value="">-- Select Terms --</option>
                            @foreach($paymentTerms ?? [] as $term)
                                @php
                                    $termId   = $term->ID ?? $term->Id ?? '';
                                    $termDesc = $term->Description ?? $term->description ?? '';
                                    $currentTerms = $award->payment_terms ?? null;
                                @endphp
                                <option value="{{ $termId }}" {{ old('payment_terms', $currentTerms) == $termId ? 'selected' : '' }}>
                                    {{ $termDesc }}
                                </option>
                            @endforeach
                        </select>
                        @error('payment_terms') <div class="invalid-feedback">{{ $message }}</div> @enderror
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

                                <div class="card border mb-3">
                                    <div class="card-header bg-light">
                                        <strong>Penalty Rule (for missed/unaccepted milestones)</strong>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Penalty Type</label>
                                                <select name="penalty_type" class="form-select">
                                                    <option value="">-- None --</option>
                                                    <option value="PER_DAY_DELAY" @selected(old('penalty_type', $penaltyRule->PenaltyType ?? null) === 'PER_DAY_DELAY')>Per Day Delay</option>
                                                    <option value="PERCENT" @selected(old('penalty_type', $penaltyRule->PenaltyType ?? null) === 'PERCENT')>Percent</option>
                                                    <option value="FIXED" @selected(old('penalty_type', $penaltyRule->PenaltyType ?? null) === 'FIXED')>Fixed</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Rate</label>
                                                <input type="number" step="0.0001" min="0" name="penalty_rate" class="form-control" value="{{ old('penalty_rate', $penaltyRule->Rate ?? null) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Grace Days</label>
                                                <input type="number" min="0" name="grace_days" class="form-control" value="{{ old('grace_days', $penaltyRule->GraceDays ?? 0) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Cap Amount</label>
                                                <input type="number" step="0.01" min="0" name="cap_amount" class="form-control" value="{{ old('cap_amount', $penaltyRule->CapAmount ?? null) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Apply Method</label>
                                                <select name="apply_method" class="form-select">
                                                    <option value="DEDUCT_FROM_PAYMENT" @selected(old('apply_method', $penaltyRule->ApplyMethod ?? 'DEDUCT_FROM_PAYMENT') === 'DEDUCT_FROM_PAYMENT')>Deduct From Payment</option>
                                                    <option value="DEBIT_NOTE" @selected(old('apply_method', $penaltyRule->ApplyMethod ?? null) === 'DEBIT_NOTE')>Debit Note</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Cap Percent</label>
                                                <input type="number" step="0.0001" min="0" name="cap_percent" class="form-control" value="{{ old('cap_percent', $penaltyRule->CapPercent ?? null) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check mt-4">
                                                    <input class="form-check-input" type="checkbox" value="1" id="requires_approval_to_apply" name="requires_approval_to_apply" @checked(old('requires_approval_to_apply', $penaltyRule->RequiresApprovalToApply ?? false))>
                                                    <label class="form-check-label" for="requires_approval_to_apply">
                                                        Approval required to apply
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check mt-4">
                                                    <input class="form-check-input" type="checkbox" value="1" id="requires_approval_to_waive" name="requires_approval_to_waive" @checked(old('requires_approval_to_waive', $penaltyRule->RequiresApprovalToWaive ?? true))>
                                                    <label class="form-check-label" for="requires_approval_to_waive">
                                                        Approval required to waive
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if(($type ?? 'tender') === 'tender')
                                    <div class="alert alert-info d-flex justify-content-between align-items-center">
                                        <div class="me-3">
                                            <strong>Milestones and checklist are managed in Contract Lifecycle Execution.</strong>
                                            <div class="small mb-0">Use the button on the right to add milestones, add checklist items, and tick fulfillment.</div>
                                        </div>
                                        <a href="{{ route('contracts.lifecycle.execution', $award->Id) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-list-check me-1"></i> Manage Milestones
                                        </a>
                                    </div>
                                @endif

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
