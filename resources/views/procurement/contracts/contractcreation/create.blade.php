@extends('layouts.app')
@section('title', 'Create Contract')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4>📝 New Contract Creation</h4>
                        @if($award)
                            <p class="text-muted mb-0">
                                Creating contract from approved award:
                                <strong>{{ $award->tender?->TenderNo ?? 'N/A' }}</strong>
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
                                            <td>{{ $award->tender?->TenderNo ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tender Title:</strong></td>
                                            <td>{{ $award->tender?->Title ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Winning Supplier:</strong></td>
                                            <td>{{ $award->winningSupplier?->thirdParty?->TradingName ?? $award->winningSupplier?->supplierMaster?->party?->TradingName ?? $award->winningSupplier?->SupplierName ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td><strong>Awarded Amount:</strong></td>
                                            <td>{{ number_format($award->AwardedAmount ?? 0, 2) }} {{ optional($award->tender?->Currency)->Code ?? $award->tender?->Currency ?? 'KES' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Award Date:</strong></td>
                                            <td>{{ $award->AwardDate ? $award->AwardDate->format('Y-m-d') : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Award Status:</strong></td>
                                            <td>
                                                <span class="badge {{ $award->status_badge['class'] }}">
                                                    {{ $award->status_badge['text'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contract Creation Form -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">📋 Contract Details</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('contracts.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="award_id" value="{{ $award->Id }}">
                                <input type="hidden" name="award_type" value="{{ $awardType ?? 'tender' }}">

                                <!-- Contract Type Selection -->
                                <div class="mb-4">
                                    <label class="form-label">Contract Management Type <span
                                            class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div
                                                class="card border {{ old('contract_type') === 'procurement_managed' || !old('contract_type') ? 'border-primary' : '' }}">
                                                <div class="card-body text-center">
                                                    <input type="radio" name="contract_type" id="procurement_managed"
                                                           value="procurement_managed" class="form-check-input"
                                                        {{ old('contract_type') === 'procurement_managed' || !old('contract_type') ? 'checked' : '' }}>
                                                    <label for="procurement_managed" class="form-check-label d-block">
                                                        <i class="fas fa-cogs fa-2x text-primary d-block mb-2"></i>
                                                        <strong>Procurement Managed</strong>
                                                        <small class="text-muted d-block">Create and manage contract
                                                            within procurement</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div
                                                class="card border {{ old('contract_type') === 'legal_managed' ? 'border-primary' : '' }}">
                                                <div class="card-body text-center">
                                                    <input type="radio" name="contract_type" id="legal_managed"
                                                           value="legal_managed" class="form-check-input"
                                                        {{ old('contract_type') === 'legal_managed' ? 'checked' : '' }}>
                                                    <label for="legal_managed" class="form-check-label d-block">
                                                        <i class="fas fa-balance-scale fa-2x text-warning d-block mb-2"></i>
                                                        <strong>Legal Department</strong>
                                                        <small class="text-muted d-block">Send request to legal
                                                            department for contract creation</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contract Basic Information -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Contract Title <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="contract_title" class="form-control"
                                               value="{{ old('contract_title', $award->tender?->Title ?? '') }}"
                                               placeholder="Enter contract title">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contract Value <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" name="contract_value" class="form-control"
                               value="{{ old('contract_value', $award->AwardedAmount ?? '') }}"
                               step="0.01" min="0" placeholder="0.00">
                        <span
                            class="input-group-text">{{ optional($award->tender?->Currency)->Code ?? $award->tender?->Currency ?? 'KES' }}</span>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Contract Description <span class="text-danger">*</span></label>
                <textarea name="contract_description" class="form-control" rows="3"
                          placeholder="Provide detailed description of contract scope and deliverables">{{ old('contract_description', $award->tender?->Description ?? '') }}</textarea>
            </div>

                                <!-- Contract Duration -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Contract Start Date <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" class="form-control"
                           value="{{ old('start_date', $award->ContractStartDate ? $award->ContractStartDate->format('Y-m-d') : date('Y-m-d')) }}">
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
                                        <label class="form-label">Payment Terms <span
                                                class="text-danger">*</span></label>
                                        <textarea name="payment_terms" class="form-control" rows="3"
                                                  placeholder="Specify payment schedule, milestones, and conditions">{{ old('payment_terms', $award->PaymentTerms ?? 'Payment upon delivery and acceptance of goods/services as per agreed milestones') }}</textarea>
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
                                                    <option value="PER_DAY_DELAY" @selected(old('penalty_type') === 'PER_DAY_DELAY')>Per Day Delay</option>
                                                    <option value="PERCENT" @selected(old('penalty_type') === 'PERCENT')>Percent</option>
                                                    <option value="FIXED" @selected(old('penalty_type') === 'FIXED')>Fixed</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Rate</label>
                                                <input type="number" step="0.0001" min="0" name="penalty_rate" class="form-control" value="{{ old('penalty_rate') }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Grace Days</label>
                                                <input type="number" min="0" name="grace_days" class="form-control" value="{{ old('grace_days', 0) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Cap Amount</label>
                                                <input type="number" step="0.01" min="0" name="cap_amount" class="form-control" value="{{ old('cap_amount') }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Apply Method</label>
                                                <select name="apply_method" class="form-select">
                                                    <option value="DEDUCT_FROM_PAYMENT" @selected(old('apply_method', 'DEDUCT_FROM_PAYMENT') === 'DEDUCT_FROM_PAYMENT')>Deduct From Payment</option>
                                                    <option value="DEBIT_NOTE" @selected(old('apply_method') === 'DEBIT_NOTE')>Debit Note</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Cap Percent</label>
                                                <input type="number" step="0.0001" min="0" name="cap_percent" class="form-control" value="{{ old('cap_percent') }}">
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check mt-4">
                                                    <input class="form-check-input" type="checkbox" value="1" id="requires_approval_to_apply" name="requires_approval_to_apply" @checked(old('requires_approval_to_apply'))>
                                                    <label class="form-check-label" for="requires_approval_to_apply">
                                                        Approval required to apply
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check mt-4">
                                                    <input class="form-check-input" type="checkbox" value="1" id="requires_approval_to_waive" name="requires_approval_to_waive" @checked(old('requires_approval_to_waive', true))>
                                                    <label class="form-check-label" for="requires_approval_to_waive">
                                                        Approval required to waive
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="row">
                                    <div class="col-md-12 text-end">
                                        <button type="button" class="btn btn-outline-secondary me-2"
                                                onclick="history.back()">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Create Contract
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <!-- No Award Selected -->
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                            <h5>No Award Selected</h5>
                            <p class="text-muted">Please select an approved award to create a contract.</p>
                            <a href="{{ route('procawards.index') }}" class="btn btn-primary">
                                <i class="fas fa-trophy"></i> View Awards
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Handle contract type selection highlighting
        document.querySelectorAll('input[name="contract_type"]').forEach(function (input) {
            input.addEventListener('change', function () {
                // Remove border from all cards
                document.querySelectorAll('.card.border').forEach(function (card) {
                    card.classList.remove('border-primary');
                });

                // Add border to selected card
                this.closest('.card').classList.add('border-primary');
            });
        });

        // Auto-calculate end date based on tender duration if available
        document.addEventListener('DOMContentLoaded', function () {
            const startDateInput = document.querySelector('input[name="start_date"]');
            const endDateInput = document.querySelector('input[name="end_date"]');

            if (startDateInput && endDateInput && !endDateInput.value) {
                startDateInput.addEventListener('change', function () {
                    if (this.value) {
                        // Default to 12 months contract duration
                        const startDate = new Date(this.value);
                        const endDate = new Date(startDate);
                        endDate.setMonth(endDate.getMonth() + 12);

                        endDateInput.value = endDate.toISOString().split('T')[0];
                    }
                });
            }
        });
    </script>
@endsection
