@extends('layouts.app')
@section('title', 'Initiate Commission Payout')

@section('content')
<div class="container mt-5" style="max-width: 900px;">
    <div class="card shadow-lg border-0 rounded-4">

        {{-- Header --}}
        <div class="card-header bg-primary text-white rounded-top-4 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="bi bi-cash-coin me-2"></i> Commission Payout</h5>
        </div>

        {{-- Body --}}
        <div class="card-body p-4">
            <form method="POST" action="{{ route('bancassurance.commissions.payouts.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- Policy --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Policy <span class="text-danger">*</span></label>
                    <select name="PolicyId" class="form-select rounded-3" required>
                        <option value="">-- Select Policy --</option>
                        @foreach ($policies as $policy)
                            <option value="{{ $policy->Id }}">{{ $policy->PolicyNumber }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Reference / Mode / Amount --}}
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Payment Mode <span class="text-danger">*</span></label>
                        <select name="PaymentMode" class="form-select rounded-3" required>
                            <option value="">-- Select Mode --</option>
                            @foreach ($paymentmodes as $paymentmode)
                                <option value="{{ $paymentmode->ID }}">{{ $paymentmode->Description }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Payout Reference <span class="text-danger">*</span></label>
                        <input type="text" name="PayoutReference" class="form-control rounded-3" placeholder="Enter reference" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Paid Amount <span class="text-danger">*</span></label>
                        <input type="number" name="PaidAmount" id="PaidAmount" 
                               class="form-control rounded-3 text-end" 
                               placeholder="e.g. 10000.00" 
                               step="0.01" min="0" required onblur="fixDecimalPlaces(this)">
                    </div>
                </div>

                {{-- Payment Date --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="PaymentDate" class="form-control rounded-3" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                </div>

                {{-- Paid By --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Paid to<span class="text-danger">*</span></label>
                        <select name="PaidBy" class="form-select rounded-3" required>
                            <option value="">-- Select User --</option>
                            @foreach ($paidBy as $user)
                                <option value="{{ $user->Id }}">{{ $user->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Remarks --}}
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Remarks <span class="text-danger">*</span></label>
                    <textarea name="Remarks" class="form-control rounded-3" rows="2" placeholder="Add notes or comments..."></textarea>
                </div>

                {{-- Footer Buttons --}}
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <a href="{{ route('bancassurance.commissions.payouts.index') }}" class="btn btn-secondary rounded-3 px-4">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-success rounded-3 px-4">
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
        input.value = val.toFixed(2); // always show two decimals
    }
}
</script>
@endsection
