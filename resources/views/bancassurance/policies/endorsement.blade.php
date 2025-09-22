@extends('layouts.app')
@section('title', 'Policy Endorsement')

@section('content')
    <div class="container mt-4">
        <h4>Endorse Policy: {{ $policy->PolicyNumber }}</h4>

        <form action="{{ route('bancassurance.policies.storeEndorsement', $policy->Id) }}" method="POST"
              enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label">Customer</label>
                <input type="text" class="form-control" value="{{ $policy->CustomerName }}" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Endorsement Type</label>
                <input type="text" class="form-control" name="EndorsementType" required
                       placeholder="e.g., Beneficiary Change, Contact Update">
            </div>

            <div class="mb-3">
                <label class="form-label">Request Date</label>
                <input type="date" class="form-control" name="RequestDate" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Effective Date</label>
                <input type="date" class="form-control" name="EffectiveDate" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="Description" rows="3"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Supporting Document (optional)</label>
                <input type="file" class="form-control" name="SupportingDocument">
            </div>

            <button type="submit" class="btn btn-primary">Submit Endorsement</button>
        </form>
    </div>
@endsection
