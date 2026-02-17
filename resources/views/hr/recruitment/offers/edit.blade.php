@extends('layouts.app')

@section('title', 'Edit Offer')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Offer</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.recruitment.offers.index') }}">Back</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('hr.recruitment.offers.update', $offer->Id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Application *</label>
                        <select name="ApplicationID" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($applications as $application)
                                <option value="{{ $application->Id }}" @selected(old('ApplicationID', $offer->ApplicationID) == $application->Id)>
                                    {{ $application->applicant?->FirstName }} {{ $application->applicant?->LastName }} - {{ $application->opening?->Title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Offer Date</label>
                        <input type="date" name="OfferDate" class="form-control" value="{{ old('OfferDate', $offer->OfferDate ? \Carbon\Carbon::parse($offer->OfferDate)->format('Y-m-d') : '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Salary Offered</label>
                        <input type="number" step="0.01" name="SalaryOffered" class="form-control" value="{{ old('SalaryOffered', $offer->SalaryOffered) }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Benefits</label>
                        <textarea name="Benefits" class="form-control" rows="3">{{ old('Benefits', $offer->Benefits) }}</textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Notes</label>
                        <textarea name="Notes" class="form-control" rows="3">{{ old('Notes', $offer->Notes) }}</textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" name="Action" value="draft" class="btn btn-outline-secondary">Save Draft</button>
                    <button type="submit" name="Action" value="submit" class="btn btn-primary">Submit for Approval</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
