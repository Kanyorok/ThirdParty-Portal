@extends('layouts.app')
@section('title', 'Set Commission Rule')

@section('content')

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Please fix the following issues:</strong>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="container mt-4">
    <div class="card shadow-sm rounded-3">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-gear-fill me-2"></i> Commission Info</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('commissions.rules.store') }}">
                @csrf

                <!-- Row 1 -->
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Rule Name <span class="text-danger">*</span></label>
                        <input type="text" name="RuleName" class="form-control rounded-3" placeholder="Enter rule name" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Product <span class="text-danger">*</span></label>
                        <select name="ProductId" class="form-select rounded-3" required>
                            <option value="">-- Select Product --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->Id }}">{{ $product->Name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Policy Type <span class="text-danger">*</span></label>
                        <select name="PolicyTypeId" class="form-select rounded-3" required>
                            <option value="">-- Select Policy Type --</option>
                            @foreach ($policytypes as $policytype)
                                <option value="{{ $policytype->ID }}">{{ $policytype->Description }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Row 2 -->
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Commission Rate (%) <span class="text-danger">*</span></label>
                        <input 
                            type="number" 
                            name="CommissionRate" 
                            step="0.01" 
                            min="0" 
                            class="form-control rounded-3 text-end" 
                            placeholder="e.g. 5.00" 
                            required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Fixed Amount <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            name="FixedAmount" 
                            id="FixedAmount" 
                            class="form-control rounded-3 text-end" 
                            placeholder="e.g. 1,000.00" 
                            required 
                            oninput="formatFixedAmount(this)">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Applies To <span class="text-danger">*</span></label>
                        <select name="AppliesTo" class="form-select rounded-3" required>
                            <option value="">-- Select Option --</option>
                            @foreach ($assignto as $assign)
                                <option value="{{ $assign->ID }}">{{ $assign->Description }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Row 3 -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="IsActive" value="1" id="primaryCheck" checked>
                        <label class="form-check-label fw-semibold" for="primaryCheck">Is Active</label>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-between align-items-center gap-2 mt-4">
                    <a href="{{ route('commissions.rules.index') }}" class="btn btn-secondary rounded-3">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-success rounded-3 px-4">
                        Save Rule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- JS for FixedAmount formatting --}}
<script>
function formatFixedAmount(input) {
    // remove commas and non-numeric characters except '.'
    let value = input.value.replace(/,/g, '').replace(/[^\d.]/g, '');
    if (value === '') return input.value = '';

    // split into whole + decimal parts
    let parts = value.split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ','); // add commas
    if (parts[1]) parts[1] = parts[1].substring(0, 2); // limit 2 decimals
    input.value = parts.join('.');
}

// before submit — strip commas to send a clean numeric value
document.querySelector('form').addEventListener('submit', function() {
    const fixedAmountInput = document.getElementById('FixedAmount');
    fixedAmountInput.value = fixedAmountInput.value.replace(/,/g, '');
});
</script>

@endsection
