@extends('layouts.app')
@section('title', 'Upload Vehicle Document')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">📂 Upload Vehicle Document</h4>

    <form action="{{ route('fleet.documents.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Vehicle</label>
                <select name="VehicleID" class="form-select" required>
                    @foreach ($vehicles as $vehicle)
                        <option value="{{ $vehicle->VehicleID }}">{{ $vehicle->RegistrationNumber }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Document Type</label>
                <select name="DocumentType" class="form-select" required>
                    <option value="">Select Type</option>
                    <option value="Insurance">Insurance</option>
                    <option value="Logbook">Logbook</option>
                    <option value="Inspection">Inspection</option>
                    <option value="Registration">Registration</option>
                    <option value="Emission Test">Emission Test</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Document Number</label>
                <input type="text" name="DocumentNumber" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="form-label">Issue Date</label>
                <input type="date" name="IssueDate" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="form-label">Expiry Date</label>
                <input type="date" name="ExpiryDate" class="form-control">
            </div>

            <div class="col-md-12">
                <label class="form-label">Upload File (PDF/Image)</label>
                <input type="file" name="DocumentFile" class="form-control" accept=".pdf,image/*">
            </div>

            <div class="col-md-12">
                <label class="form-label">Notes</label>
                <textarea name="Notes" class="form-control" rows="2"></textarea>
            </div>
        </div>

        <div class="mt-4">
            <button class="btn btn-primary" type="submit">📁 Save Document</button>
        </div>
    </form>
</div>
@endsection
