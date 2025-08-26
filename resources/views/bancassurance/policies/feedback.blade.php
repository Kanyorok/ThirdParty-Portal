@extends('layouts.app')
@section('title', 'Underwriting Feedback')

@section('content')
<div class="container mt-4">
    <h4>Underwriting Feedback – Policy #{{ $policy->PolicyNumber }}</h4>
    <p><strong>Customer:</strong> {{ $policy->customer->FullName }}</p>

        <form method="POST" action="{{ route('bancassurance.policies.feedback.store', $policy->Id) }}">
            @csrf

        {{-- Underwriter Name (optional or from auth?) --}}
        <div class="mb-3">
            <p><strong>Underwriter Name:</strong> {{ $policy->insurer->Name }}</p>
        </div>

        {{-- Feedback Date --}}
        <div class="mb-3">
            <label class="form-label">Feedback Date</label>
            <input type="date" name="FeedbackDate" class="form-control @error('FeedbackDate') is-invalid @enderror"
                   value="{{ old('FeedbackDate', \Carbon\Carbon::now()->format('d/m/Y')) }}" required>
            @error('FeedbackDate')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Decision --}}
        <div class="mb-3">
            <label class="form-label">Decision</label>
            <select name="Decision" class="form-select @error('Decision') is-invalid @enderror" required>
                <option value="">-- Select Decision --</option>
                @foreach ($decisions as $decision)
                    <option value="{{ $decision->ID }}" {{ old('Decision') == $decision->ID ? 'selected' : '' }}>
                        {{ $decision->Description }}
                    </option>
                @endforeach
            </select>
            @error('Decision')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Risk Score --}}
        <div class="mb-3">
            <label class="form-label">Risk Score (%)</label>
            <input type="number" name="RiskScore" class="form-control" min="0" max="100" required>
        </div>

        {{-- Comments --}}
        <div class="mb-3">
            <label class="form-label">Comments</label>
            <textarea name="Comments" class="form-control @error('Comments') is-invalid @enderror" rows="4">{{ old('Comments') }}</textarea>
            @error('Comments')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="text-end">
            <button class="btn btn-success">Submit Feedback</button>
        </div>
    </form>
</div>
@endsection
