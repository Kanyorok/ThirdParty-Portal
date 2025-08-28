@extends('layouts.app')
@section('title', 'Add Insurance Record')
@section('content')
    <div class="card shadow p-4 rounded-4">
        <h4 class="mb-4">➕ Add Insurance Record</h4>
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>⚠️ Please fix the following errors:</strong>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('fleet.insurance_tracker.store') }}" enctype="multipart/form-data" method="POST">
            @csrf

            <div class="mb-3">
                <label for="VehicleID" class="form-label">Vehicle</label>
                <select name="VehicleID" id="VehicleID" class="form-select" required>
                    <option value="">-- Select Vehicle --</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v->Id }}">{{ $v->RegistrationNo }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="PolicyNumber" class="form-label">Policy Number</label>
                <input type="number" class="form-control" name="PolicyNumber" required>
            </div>

            <div class="mb-3">
                <label for="InsuranceProvider" class="form-label">Insurance Provider</label>
                <select class="form-select" name="InsuranceProvider" required>
                    <option value="">Select Provider</option>
                    @foreach ($insuranceProvider as $provider)
                        <option
                            value="{{ $provider->Id }}" {{ old('InsuranceProvider') == $provider->Id ? 'selected' : '' }}>
                            {{ $provider->Name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="PremiumAmount" class="form-label">Premium Amount</label>
                <input type="number" step="0.01" class="form-control" name="PremiumAmount" required>
            </div>

            <div class="mb-3">
                <label for="CoverageStartDate" class="form-label">Start Date</label>
                <input type="date" class="form-control" name="CoverageStartDate" required>
            </div>

            <div class="mb-3">
                <label for="CoverageEndDate" class="form-label">Expiry Date</label>
                <input type="date" class="form-control" name="CoverageEndDate" required>
            </div>

            <div class="mb-3">
                <label for="RenewalReminderDate" class="form-label">Renewal Reminder Date</label>
                <input type="date" class="form-control" name="RenewalReminderDate" required>
            </div>

            <div class="mb-3">
                <label for="Status" class="form-label">Status</label>
                <select class="form-select" name="Status" required>
                    <option value="">Select Type</option>
                    @foreach ($insuranceStatuses as $status)
                        <option value="{{ $status->ID }}" {{ old('Status') == $status->ID ? 'selected' : '' }}>
                            {{ $status->Description }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="Notes" class="form-label">Notes</label>
                <textarea name="Notes" class="form-control" rows="3"></textarea>
            </div>

            <!-- Document Upload -->
      <div class="mb-3">
        <label class="form-label">Upload Supporting Documents</label>
        
        <input type="file" name="Document" class="form-control" multiple>
        <small class="text-muted">e.g. ID copy, Certificate of Incorporation</small>
      </div>

            <button type="submit" class="btn btn-success">💾 Save Insurance Record</button>
        </form>

    </div>
@endsection
