@extends('layouts.app')
@section('title', 'Edit Commission Rule')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm rounded-3">
        <div class="card-header bg-info text-dark">
            <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Commission Rule</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('commissions.rules.update', $rule->Id) }}">
                @csrf
                @method('PUT')

                <!-- Row 1 -->
                <div class="row g-3 mb-3">
                    <!-- Rule Name -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Rule Name <span class="text-danger">*</span></label>
                        <input type="text" name="RuleName" class="form-control rounded-3"
                               value="{{ old('RuleName', $rule->RuleName) }}" placeholder="Enter rule name" required>
                    </div>

                    <!-- Product -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Product <span class="text-danger">*</span></label>
                        <select name="ProductId" class="form-select rounded-3" required>
                            <option value="">-- Select Product --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->Id }}"
                                    {{ $rule->ProductId == $product->Id ? 'selected' : '' }}>
                                    {{ $product->Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Policy Type -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Policy Type <span class="text-danger">*</span></label>
                        <select name="PolicyTypeId" class="form-select rounded-3" required>
                            <option value="">-- Select Policy Type --</option>
                            @foreach ($policytypes as $policytype)
                                <option value="{{ $policytype->ID }}"
                                    {{ $rule->PolicyTypeId == $policytype->ID ? 'selected' : '' }}>
                                    {{ $policytype->Description }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Row 2 -->
                <div class="row g-3 mb-3">
                    <!-- Commission Rate -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Commission Rate (%) <span
                                class="text-danger">*</span></label>
                        <input type="number" name="CommissionRate" step="0.01" class="form-control rounded-3"
                               value="{{ old('CommissionRate', $rule->CommissionRate) }}" placeholder="e.g. 5.00"
                               required>
                    </div>

                    <!-- Fixed Amount -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Fixed Amount <span class="text-danger">*</span></label>
                        <input type="number" name="FixedAmount" step="0.01" class="form-control rounded-3"
                               value="{{ old('FixedAmount', $rule->FixedAmount) }}" placeholder="e.g. 1000" required>
                    </div>

                    <!-- Applies To -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Applies To <span class="text-danger">*</span></label>
                        <select name="AppliesTo" class="form-select rounded-3" required>
                            <option value="">-- Select Option --</option>
                            @foreach ($assignto as $assign)
                                <option value="{{ $assign->ID }}"
                                    {{ $rule->AppliesTo == $assign->ID ? 'selected' : '' }}>
                                    {{ $assign->Description }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Row 3 -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="IsActive" value="1" id="primaryCheck"
                            {{ $rule->IsActive ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="primaryCheck">Is Active</label>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('commissions.rules.index') }}" class="btn btn-secondary rounded-3">
                        <i class="bi bi-arrow-left-circle me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary text-dark rounded-3 px-4">
                        <i class="bi bi-save me-1"></i> Update Rule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
