@extends('layouts.app')
@section('title', 'Edit Commission Rule')

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
                <i class="bi bi-pencil-square me-2"></i>Edit Commission Rule
            </h5>
        </div>

        {{-- Body --}}
        <div class="card-body p-4">
            <form method="POST" action="{{ route('commissions.rules.update', $rule->Id) }}">
                @csrf
                @method('PUT')

                {{-- ================= BASIC DETAILS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Rule Details</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small ">
                                Rule Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="RuleName"
                                   class="form-control form-control-sm"
                                   value="{{ old('RuleName', $rule->RuleName) }}"
                                   required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">
                                Product <span class="text-danger">*</span>
                            </label>
                            <select name="ProductId"
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select Product --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->Id }}"
                                        {{ $rule->ProductId == $product->Id ? 'selected' : '' }}>
                                        {{ $product->Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">
                                Policy Type <span class="text-danger">*</span>
                            </label>
                            <select name="PolicyTypeId"
                                    class="form-select form-select-sm"
                                    required>
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
                </div>

                {{-- ================= COMMISSION SETTINGS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Commission Settings</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small ">
                                Commission Rate (%) <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   name="CommissionRate"
                                   step="0.01"
                                   class="form-control form-control-sm text-end"
                                   value="{{ old('CommissionRate', $rule->CommissionRate) }}"
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
                                @foreach ($currencies as $curr)
                                    <option value="{{ $curr->Id }}"
                                        {{ $rule->CurrencyId == $curr->Id ? 'selected' : '' }}>
                                        {{ $curr->Code }} - {{ $curr->SymbolNative }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">
                                Fixed Amount <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   name="FixedAmount"
                                   step="0.01"
                                   class="form-control form-control-sm text-end"
                                   value="{{ old('FixedAmount', $rule->FixedAmount) }}"
                                   required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">
                                Applies To <span class="text-danger">*</span>
                            </label>
                            <select name="AppliesTo"
                                    class="form-select form-select-sm"
                                    required>
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
                </div>

                {{-- ================= STATUS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Status</h6>

                    <div class="form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               name="IsActive"
                               value="1"
                               id="IsActive"
                            {{ $rule->IsActive ? 'checked' : '' }}>
                        <label class="form-check-label " for="IsActive">
                            Active Rule
                            <small class="text-muted d-block">
                                Disable to stop commission calculation
                            </small>
                        </label>
                    </div>
                </div>

                {{-- ================= ACTIONS ================= --}}
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="{{ route('commissions.rules.index') }}"
                       class="btn btn-sm btn-outline-secondary px-4">
                        Back
                    </a>
                    <button type="submit"
                            class="btn btn-sm btn-success px-4">
                        <i class="bi bi-save me-1"></i> Update Rule
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection
