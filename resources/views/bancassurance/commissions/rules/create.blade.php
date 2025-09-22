@extends('layouts.app')
@section('title', 'Set Commission Rule')

@section('content')
    <div class="container mt-4">
        <form method="POST" action="{{ route('commissions.rules.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label"> Rule Name <span class="text-danger">*</span></label>
                <input type="text" name="RuleName" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label"> Product <span class="text-danger">*</span></label>
                <select name="ProductId" class="form-select" required>
                    <option value="">-- Select --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->Id }}">{{ $product->Name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Policy Type <span class="text-danger">*</span></label>
                <select name="PolicyTypeId" class="form-select" required>
                    <option value="">--Select Policy Type--</option>
                    @foreach ($policytypes as $policytype)
                        <option value="{{ $policytype->ID }}">
                            {{ $policytype->Description }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label"> CommissionRate (%) <span class="text-danger">*</span></label>
                <input type="number" name="CommissionRate" step="0.01" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label"> FixedAmount <span class="text-danger">*</span></label>
                <input type="number" name="FixedAmount" step="0.01" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Applies To <span class="text-danger">*</span></label>
                <select name="AppliesTo" class="form-select" required>
                    <option value="">--Select--</option>
                    @foreach ($assignto as $assign)
                        <option value="{{ $assign->ID }}">
                            {{ $assign->Description }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3 form-check">
                <input class="form-check-input" type="checkbox" name="IsActive" value="1" id="primaryCheck">
                <label class="form-check-label" for="primaryCheck">IsActive </label>
            </div>

            <button type="submit" class="btn btn-success">Save Rule</button>
        </form>
    </div>
@endsection
