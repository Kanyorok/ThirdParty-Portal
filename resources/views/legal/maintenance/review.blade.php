@extends('layouts.app')
@section('title', 'Legal Review')

@section('content')
<div class="card shadow p-4 rounded-4">
    <h4 class="mb-4">✅ Legal Review – {{ $contract->DocumentTitle }}</h4>

    <form method="POST" action="{{ route('legal.contracts.submitReview', $contract->ID) }}">
        @csrf

        <div class="mb-3">
            <label for="ReviewStatus" class="form-label">Review Status</label>
            <select name="ReviewStatus" id="ReviewStatus" class="form-select" required>
                <option value="">-- Select Status --</option>
                @foreach(['Draft', 'Reviewed', 'Approved', 'Rejected'] as $status)
                    <option value="{{ $status }}" {{ $contract->ReviewStatus == $status ? 'selected' : '' }}>
                        {{ $status }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="Remarks" class="form-label">Remarks</label>
            <textarea name="Remarks" id="Remarks" rows="4" class="form-control">{{ old('Remarks', $contract->Remarks) }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Submit Review</button>
        <a href="{{ route('legal.contracts.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
