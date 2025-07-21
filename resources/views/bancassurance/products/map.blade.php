@extends('layouts.app')
@section('title', 'Map Product to Insurer')

@section('content')
<div class="container mt-4">
    <h4>🔗 Map Product – {{ $product->Name }}</h4>

    <form method="POST" action="{{ route('bancassurance.products.map.store', $product->Id) }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Insurance Provider</label>
            <select name="InsuranceProviderID" class="form-select" required>
                <option value="">-- Select Provider --</option>
                @foreach($providers as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Policy Type</label>
            <select name="PolicyTypeID" class="form-select">
                <option value="">-- Optional --</option>
                @foreach($policyTypes as $id => $desc)
                    <option value="{{ $id }}">{{ $desc }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Custom Name (as used by insurer)</label>
            <input type="text" name="CustomName" class="form-control" maxlength="150">
        </div>

        <div class="mb-3">
            <label class="form-label">Commission Type</label>
            <select name="CommissionType" class="form-select" required>
                <option value="">-- Select --</option>
                <option value="Flat">Flat</option>
                <option value="Tiered">Tiered</option>
            </select>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-primary">✅ Map Product</button>
        </div>
    </form>
</div>
@endsection
