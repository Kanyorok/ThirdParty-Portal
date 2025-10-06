@extends('layouts.app')
@section('title', 'Edit Bid Responsiveness')
@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5>📝 Edit Bid Responsiveness</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('bidresponsiveness.update', $bid) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <!-- Bidder Information -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Bidder</label>
                                    <input type="text" class="form-control" value="{{ $tenderSupplier->supplier->thirdParty->ThirdPartyName ?? 'N/A' }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tender Reference</label>
                                    <input type="text" class="form-control" value="{{ $tenderSupplier->tender->TenderNo ?? 'N/A' }}" readonly>
                                </div>
                            </div>

                            <!-- Responsiveness Criteria -->
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Was Submission Timely?</label>
                                    <select class="form-select" name="SubmittedTimely" required>
                                        <option value="" disabled>-- Select --</option>
                                        <option value="1" {{ old('SubmittedTimely', $bid->SubmittedTimely) == 1 ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ old('SubmittedTimely', $bid->SubmittedTimely) == 0 ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Mandatory Documents Complete?</label>
                                    <select class="form-select" name="HasMandatoryDocuments" required>
                                        <option value="" disabled>-- Select --</option>
                                        <option value="1" {{ old('HasMandatoryDocuments', $bid->HasMandatoryDocuments) == 1 ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ old('HasMandatoryDocuments', $bid->HasMandatoryDocuments) == 0 ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Meets Eligibility Criteria?</label>
                                    <select class="form-select" name="IsEligible" required>
                                        <option value="" disabled>-- Select --</option>
                                        <option value="1" {{ old('IsEligible', $bid->IsEligible) == 1 ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ old('IsEligible', $bid->IsEligible) == 0 ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="form-label">Remarks</label>
                                <textarea class="form-control" name="Remarks" rows="3" placeholder="E.g. Missing tax clearance certificate">{{ old('Remarks', $bid->Remarks) }}</textarea>
                            </div>

                            <div class="mt-4 d-flex gap-2">
                                <button class="btn btn-success" name="IsResponsive" value="1">
                                    <i class="fas fa-check"></i> Mark as Responsive
                                </button>
                                <button class="btn btn-danger" name="IsResponsive" value="0">
                                    <i class="fas fa-times"></i> Mark as Non-Responsive
                                </button>
                                <a href="{{ route('bid-responsiveness.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.querySelector('form').addEventListener('submit', function (e) {
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
