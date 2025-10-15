@extends('layouts.app')
@section('title', 'Medical Fund Contribution')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">
        <i class="bi bi-person-heart me-2"></i>
        New Contribution: <span class="text-primary">{{ $medical_fund->FundName }}</span>
    </h4>

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <strong>There were validation errors:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Contribution Form --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <form id="contributionForm" action="{{ route('bancassurance.medicalfunds.contributions.store', $medical_fund->Id) }}" method="POST">
                @csrf

                <div class="row g-3">
                    {{-- Contribution Date --}}
                    <div class="col-md-3">
                        <label for="ContributionDate" class="form-label">
                            Date <span class="text-danger">*</span>
                        </label>
                        <input 
                            type="date" 
                            id="ContributionDate" 
                            name="ContributionDate" 
                            class="form-control" 
                            value="{{ old('ContributionDate', now()->format('Y-m-d')) }}" 
                            required
                        >
                    </div>

                    {{-- Contributor Type --}}
                    <div class="col-md-3">
                        <label for="ContributorType" class="form-label">
                            Contributor Type <span class="text-danger">*</span>
                        </label>
                        <select 
                            id="ContributorType" 
                            name="ContributorType" 
                            class="form-select" 
                            required
                        >
                            <option value="" disabled {{ old('ContributorType') ? '' : 'selected' }}>
                                Select Contributor Type
                            </option>
                            @foreach($contributortypes as $type)
                                <option 
                                    value="{{ $type->ID }}" 
                                    {{ old('ContributorType') == $type->ID ? 'selected' : '' }}
                                >
                                    {{ $type->Description }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Contributor --}}
                    <div class="col-md-3">
                        <label for="ContributorId" class="form-label">Contributor</label>
                        <select 
                            id="ContributorId" 
                            name="ContributorId" 
                            class="form-select"
                        >
                            <option value="" disabled {{ old('ContributorId') ? '' : 'selected' }}>
                                Select Contributor
                            </option>
                            @foreach($contributors as $contributor)
                                <option 
                                    value="{{ $contributor->Id }}" 
                                    {{ old('ContributorId') == $contributor->Id ? 'selected' : '' }}
                                >
                                    {{ $contributor->ThirdPartyName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Amount --}}
                    <div class="col-md-3">
                        <label for="Amount" class="form-label">
                            Amount <span class="text-danger">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="Amount" 
                            name="Amount" 
                            class="form-control text-end" 
                            value="{{ old('Amount') }}" 
                            placeholder="0.00"
                            required
                        >
                    </div>

                    {{-- Notes --}}
                    <div class="col-12">
                        <label for="Notes" class="form-label">Notes</label>
                        <input 
                            type="text" 
                            id="Notes"
                            name="Notes" 
                            class="form-control" 
                            value="{{ old('Notes') }}"
                            placeholder="Optional remarks about this contribution..."
                        >
                    </div>
                </div>

                {{-- Actions --}}
                <div class="mt-4 d-flex justify-content-start gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Save
                    </button>
                    <a 
                        href="{{ route('bancassurance.medicalfunds.contributions.index', $medical_fund->Id) }}" 
                        class="btn btn-outline-secondary"
                    >
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

{{-- 💡 Live Amount Formatting Script --}}
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('Amount');
    const form = document.getElementById('contributionForm');

    // Format as user types
    amountInput.addEventListener('input', function() {
        let rawValue = this.value.replace(/,/g, '');
        if (!isNaN(rawValue) && rawValue !== '') {
            const parts = rawValue.split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            this.value = parts.join('.');
        }
    });

    // Remove commas before form submission
    form.addEventListener('submit', function() {
        amountInput.value = amountInput.value.replace(/,/g, '');
    });
});
</script>
@endsection
