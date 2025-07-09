@extends('layouts.app')
@section('title', 'Bid Responsiveness Review')
@section('content')
<div class="container mt-4">
    <h4>📋 Bid Responsiveness Review</h4>
    <form action="{{ route('bidresponsiveness.store') }}" method="POST">
        @csrf
        <input type="hidden" name="TenderSupplierID" value="{{ $tenderSupplier->id }}">
        <div class="mb-3">
            <label class="form-label">Bidder</label>
            <input type="text" class="form-control" value="{{ $tenderSupplier->supplier->SupplierName ?? 'N/A' }}" readonly>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Was Submission Timely?</label>
                <select class="form-select" name="SubmittedTimely" required>
                    <option value="" disabled {{ old('SubmittedTimely', $bid->SubmittedTimely ?? '') === '' ? 'selected' : '' }}>-- Select --</option>
                    <option value="1" {{ old('SubmittedTimely', $bid->SubmittedTimely ?? '') == 1 ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ old('SubmittedTimely', $bid->SubmittedTimely ?? '') == 0 ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Mandatory Documents Complete?</label>
                <select class="form-select" name="HasMandatoryDocuments" required>
                    <option value="" disabled {{ old('HasMandatoryDocuments', $bid->HasMandatoryDocuments ?? '') === '' ? 'selected' : '' }}>-- Select --</option>
                    <option value="1" {{ old('HasMandatoryDocuments', $bid->HasMandatoryDocuments ?? '') == 1 ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ old('HasMandatoryDocuments', $bid->HasMandatoryDocuments ?? '') == 0 ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Meets Eligibility Criteria?</label>
                <select class="form-select" name="IsEligible" required>
                    <option value="" disabled {{ old('IsEligible', $bid->IsEligible ?? '') === '' ? 'selected' : '' }}>-- Select --</option>
                    <option value="1" {{ old('IsEligible', $bid->IsEligible ?? '') == 1 ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ old('IsEligible', $bid->IsEligible ?? '') == 0 ? 'selected' : '' }}>No</option>
                </select>
            </div>
        </div>

        <div class="mt-3">
            <label class="form-label">Remarks</label>
            <textarea class="form-control" name="Remarks" rows="3" placeholder="E.g. Missing tax clearance certificate">
                {{ old('Remarks', $bid->Remarks ?? '') }}
            </textarea>
        </div>

        <div class="mt-3 text-end">
            <button class="btn btn-primary" name="IsResponsive" value="1">Mark as Responsive</button>
            <button class="btn btn-danger" name="IsResponsive" value="0">Mark as Non-Responsive</button>
        </div>
    </form>
</div>
<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        const fields = ['SubmittedTimely', 'HasMandatoryDocuments', 'IsEligible'];
        let allSelected = true;

        fields.forEach(name => {
            const value = document.querySelector(`[name="${name}"]`).value;
            if (value === '') {
                allSelected = false;
            }
        });

        if (!allSelected) {
            e.preventDefault();
            alert('Please select Yes or No for all fields before proceeding.');
        }
    });
</script>

@endsection
