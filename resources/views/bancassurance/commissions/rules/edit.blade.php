@extends('layouts.app')
@section('title', 'Edit Commission Rule')

@section('content')
    <div class="container mt-4">

        <form method="POST" action="{{ route('commissions.rules.update', $rule->Id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label"> Rule Name <span class="text-danger">*</span></label>
                <input type="text" name="RuleName" class="form-control"
                       value="{{ old('RuleName', $rule->RuleName) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label"> Product <span class="text-danger">*</span></label>
                <select name="ProductId" class="form-select" required>
                    <option value="">-- Select --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->Id }}"
                            {{ $rule->ProductId == $product->Id ? 'selected' : '' }}>
                            {{ $product->Name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label"> Policy Type <span class="text-danger">*</span></label>
                <select name="PolicyTypeId" class="form-select" required>
                    <option value="">-- Select Policy Type --</option>
                    @foreach ($policytypes as $policytype)
                        <option value="{{ $policytype->ID }}"
                            {{ $rule->PolicyTypeId == $policytype->ID ? 'selected' : '' }}>
                            {{ $policytype->Description }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label"> Commission Rate (%) <span class="text-danger">*</span></label>
                <input type="number" name="CommissionRate" step="0.01" class="form-control"
                       value="{{ old('CommissionRate', $rule->CommissionRate) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label"> Fixed Amount <span class="text-danger">*</span></label>
                <input type="number" name="FixedAmount" step="0.01" class="form-control"
                       value="{{ old('FixedAmount', $rule->FixedAmount) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label"> Applies To <span class="text-danger">*</span></label>
                <select name="AppliesTo" class="form-select" required>
                    <option value="">-- Select --</option>
                    @foreach ($assignto as $assign)
                        <option value="{{ $assign->ID }}"
                            {{ $rule->AppliesTo == $assign->ID ? 'selected' : '' }}>
                            {{ $assign->Description }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3 form-check">
                <input class="form-check-input" type="checkbox" name="IsActive" value="1" id="primaryCheck"
                    {{ $rule->IsActive ? 'checked' : '' }}>
                <label class="form-check-label" for="primaryCheck"> Is Active </label>
            </div>

            <button type="submit" class="btn btn-primary">Update Rule</button>
            <a href="{{ route('commissions.rules.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
@endsection
