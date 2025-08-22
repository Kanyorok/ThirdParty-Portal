@extends('layouts.app')
@section('title', 'Add Rider')

@section('content')
    <div class="container mt-4">
        <h4>➕ Add Rider / Add-on</h4>

        <form method="POST" action="{{ route('bancassurance.riders.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Select Provider Product</label>
                <select name="ProviderProductID" class="form-select" required>
                    <option value="">-- Select --</option>
                    @foreach($mappedProducts as $mp)
                        <option value="{{ $mp->Id }}">{{ $mp->MappedProduct }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Rider Name</label>
                <input type="text" name="RiderName" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description (optional)</label>
                <textarea name="Description" class="form-control" rows="2"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Additional Premium (KES)</label>
                <input type="number" name="AdditionalPremium" class="form-control" step="0.01" min="0">
            </div>

            <div class="mb-3">
                <label class="form-label">Is this rider optional?</label>
                <select name="IsOptional" class="form-select" required>
                    <option value="1" selected>Yes</option>
                    <option value="0">No</option>
                </select>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-success">✅ Save Rider</button>
            </div>
        </form>
    </div>
@endsection
