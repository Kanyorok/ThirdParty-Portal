@extends('layouts.app')
@section('title', 'Map Product to Provider')

@section('content')
<div class="container mt-4">
    <h4>🛠️ Map Product to Provider</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('bancassurance.products.mapped.store') }}">
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
            <label class="form-label">Insurance Product</label>
            <select name="ProductID" class="form-select" required>
                <option value="">-- Select Product --</option>
                    @foreach($products as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Policy Type</label>
            <select name="PolicyTypeID" class="form-select" required>
                <option value="">-- Select Type --</option>
                @foreach($policyTypes as $id => $description)
                    <option value="{{ $id }}">{{ $description }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Custom Name (Optional)</label>
            <input type="text" class="form-control" name="CustomName" placeholder="Custom product name">
        </div>

        <div class="mb-3">
            <label class="form-label">Commission Type</label>
            <select name="CommissionType" class="form-select" required>
                <option value="">-- Select Type --</option>
                <option value="Flat">Flat</option>
                <option value="Tiered">Tiered</option>
            </select>
        </div>

        <div class="text-end">
            <button class="btn btn-success">✅ Map Product</button>
        </div>
    </form>
</div>
@endsection
