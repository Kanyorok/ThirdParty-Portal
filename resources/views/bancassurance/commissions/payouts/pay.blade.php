@extends('layouts.app')
@section('title', 'Commission Payout')

@section('content')

{{-- ================= STYLES ================= --}}
<style>
    .section-title {
        color: #000;
        font-weight: 600;
        font-size: .9rem;
        padding-bottom: .35rem;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 1rem;
    }
</style>

<div class="container mt-4" style="max-width: 900px;">
    <div class="card shadow-lg border-0 rounded-4">

        {{-- Header --}}
        <div class="card-header bg-primary border-bottom">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-cash-coin me-2"></i>Initiate Commission Payout
            </h5>
        </div>

        {{-- Body --}}
        <div class="card-body p-4">
            <form method="POST"
                  action="{{ route('bancassurance.commissions.payouts.store') }}"
                  enctype="multipart/form-data">
                @csrf

                {{-- ================= POLICY & RULE ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Policy & Commission Rule</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small ">
                                Policy <span class="text-danger">*</span>
                            </label>
                            <select name="PolicyId"
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select Policy --</option>
                                @foreach ($policies as $policy)
                                    <option value="{{ $policy->Id }}">
                                        {{ $policy->PolicyNumber }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">
                                Commission Rule
                            </label>
                            <select name="CommissionRuleId"
                                    class="form-select form-select-sm">
                                <option value="">-- Select Commission Rule --</option>
                                @foreach ($commissionRules as $rule)
                                    <option value="{{ $rule->Id }}">
                                        {{ $rule->RuleName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ================= PAYMENT DETAILS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Payment Details</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small ">
                                Payment Mode <span class="text-danger">*</span>
                            </label>
                            <select name="PaymentMode"
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select Mode --</option>
                                @foreach ($paymentmodes as $paymentmode)
                                    <option value="{{ $paymentmode->ID }}">
                                        {{ $paymentmode->Description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">
                                Payout Reference <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="PayoutReference"
                                   class="form-control form-control-sm"
                                   placeholder="Transaction / voucher reference"
                                   required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">
                                Currency <span class="text-danger">*</span>
                            </label>
                            <select name="CurrencyId"
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select Currency --</option>
                                @foreach ($currencies as $currency)
                                    <option value="{{ $currency->Id }}">
                                        {{ $currency->Code }} - {{ $currency->SymbolNative }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">
                                Paid Amount <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   name="PaidAmount"
                                   id="PaidAmount"
                                   class="form-control form-control-sm text-end"
                                   step="0.01"
                                   min="0"
                                   placeholder="0.00"
                                   required
                                   onblur="fixDecimalPlaces(this)">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">
                                Payment Date <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   name="PaymentDate"
                                   class="form-control form-control-sm"
                                   value="{{ now()->format('Y-m-d') }}"
                                   required>
                        </div>
                    </div>
                </div>

                {{-- ================= PAYEE ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Payee Information</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small ">
                                Paid To <span class="text-danger">*</span>
                            </label>
                            <select name="PaidTo"
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select User --</option>
                                @foreach ($PaidTo as $user)
                                    <option value="{{ $user->Id }}">
                                        {{ $user->Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ================= REMARKS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Remarks</h6>

                    <textarea name="Remarks"
                              class="form-control form-control-sm"
                              rows="2"
                              placeholder="Additional notes or justification for this payout..."></textarea>
                </div>

                {{-- ================= ACTIONS ================= --}}
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="{{ route('bancassurance.commissions.payouts.index') }}"
                       class="btn btn-sm btn-outline-secondary px-4">
                        Cancel
                    </a>
                    <button type="submit"
                            class="btn btn-sm btn-success px-4">
                        <i class="bi bi-check-circle me-1"></i> Submit Payout
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- JS for consistent decimal places --}}
<script>
    function fixDecimalPlaces(input) {
        let val = parseFloat(input.value);
        if (!isNaN(val)) {
            input.value = val.toFixed(2);
        }
    }
</script>

@endsection
