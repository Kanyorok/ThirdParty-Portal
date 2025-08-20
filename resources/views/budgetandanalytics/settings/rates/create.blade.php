@extends('layouts.app')
@section('title', 'Add Budget Rate Type')

@section('content')
    <div class="card p-4">
{{--        <h5 class="mb-4">➕ Add Budget Rate Type</h5>--}}

        <form method="POST" action="{{ route('rates.store') }}">
            @csrf

            <div class="mb-3">
                <label for="RateTypeCode" class="form-label">Rate Type Code</label>
                <input type="text" name="RateTypeCode" id="RateTypeCode" class="form-control"
                       placeholder="e.g., RATE-001" required>
            </div>

            <div class="mb-3">
                <label for="RateTypeName" class="form-label">Rate Type Name</label>
                <input type="text" name="RateTypeName" id="RateTypeName" class="form-control"
                       placeholder="e.g., Interest Rate" required>
            </div>

            <div class="mb-3">
                <label for="Description" class="form-label">Description</label>
                <textarea name="Description" id="Description" rows="3" class="form-control"
                          placeholder="Optional notes or usage..."></textarea>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" name="IsDefault" id="IsDefault" class="form-check-input" value="1">
                <label for="IsDefault" class="form-check-label">Set as Default Rate</label>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-success"
                        onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">💾 Save
                </button>
                <a href="{{ route('rates.index') }}" class="btn btn-secondary">↩️ Cancel</a>
            </div>
        </form>
    </div>
@endsection
