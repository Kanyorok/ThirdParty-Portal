@extends('layouts.app')
@section('title', 'Set Commission Rule')

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

    {{-- ================= ERRORS ================= --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm">
            <strong>Please fix the following issues:</strong>
            <ul class="mb-0 mt-2 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-lg border-0 rounded-4">

        {{-- Header --}}
        <div class="card-header border-bottom bg-primary">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-gear-fill me-2"></i>Commission Rule
            </h5>
        </div>

        {{-- Body --}}
        <div class="card-body p-4">
            <form method="POST" action="{{ route('commissions.rules.store') }}">
                @csrf

                {{-- ================= COMMISSION DETAILS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Commission Details</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small ">Rule Name <span class="text-danger">*</span></label>
                            <input type="text" name="RuleName" class="form-control form-control-sm" placeholder="Enter rule name" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">Product <span class="text-danger">*</span></label>
                            <select name="ProductId" class="form-select form-select-sm" required>
                                <option value="">-- Select Product --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->Id }}">{{ $product->Name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">Policy Type <span class="text-danger">*</span></label>
                            <select name="PolicyTypeId" class="form-select form-select-sm" required>
                                <option value="">-- Select Policy Type --</option>
                                @foreach ($policytypes as $policytype)
                                    <option value="{{ $policytype->ID }}">{{ $policytype->Description }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ================= COMMISSION RATES ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Commission Rates</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small ">Commission Rate (%) <span class="text-danger">*</span></label>
                            <input type="number" name="CommissionRate" step="0.01" min="0"
                                class="form-control form-control-sm text-end"
                                placeholder="e.g. 5.00" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">Currency <span class="text-danger">*</span></label>
                            <select name="CurrencyId" class="form-select form-select-sm" required>
                                <option value="">-- Select Currency --</option>
                                @foreach ($currencies as $curr)
                                    <option value="{{ $curr->Id }}">{{ $curr->Code }} - {{ $curr->SymbolNative }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">Fixed Amount <span class="text-danger">*</span></label>
                            <input type="text" name="FixedAmount" id="FixedAmount"
                                   class="form-control form-control-sm text-end"
                                   placeholder="e.g. 1,000.00"
                                   required
                                   oninput="formatFixedAmount(this)">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">Applies To <span class="text-danger">*</span></label>
                            <select name="AppliesTo" class="form-select form-select-sm" required>
                                <option value="">-- Select Option --</option>
                                @foreach ($assignto as $assign)
                                    <option value="{{ $assign->ID }}">{{ $assign->Description }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ================= STATUS ================= --}}
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="IsActive" value="1" id="primaryCheck" checked>
                        <label class="form-check-label " for="primaryCheck">Is Active</label>
                    </div>
                </div>

                {{-- ================= ACTIONS ================= --}}
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="{{ route('commissions.rules.index') }}" class="btn btn-sm btn-outline-secondary px-4">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-sm btn-success px-4">
                        Save Rule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================= JS: FixedAmount Formatting ================= --}}
<script>
function formatFixedAmount(input) {
    let value = input.value.replace(/,/g, '').replace(/[^\d.]/g, '');
    if (value === '') return input.value = '';
    let parts = value.split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    if (parts[1]) parts[1] = parts[1].substring(0, 2);
    input.value = parts.join('.');
}

document.querySelector('form').addEventListener('submit', function() {
    const fixedAmountInput = document.getElementById('FixedAmount');
    fixedAmountInput.value = fixedAmountInput.value.replace(/,/g, '');
});
</script>

@endsection
