@extends('layouts.app')
@section('title', 'Edit Insurance Record')

@section('content')
<div class="card shadow p-4 rounded-4">
    <h4 class="mb-4">✏️ Edit Insurance Record</h4>

    <form action="{{ route('fleet.insurance_tracker.update', $record->Id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="VehicleID" class="form-label">Vehicle</label>
            <select name="VehicleID" id="VehicleID" class="form-select" required>
                <option value="">-- Select Vehicle --</option>
                @foreach($vehicles as $v)
                    <option value="{{ $v->Id }}" {{ old('VehicleID', $record->VehicleID) == $v->Id ? 'selected' : '' }}>
                        {{ $v->RegistrationNo }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="PolicyNumber" class="form-label">Policy Number</label>
            <input type="number" class="form-control" name="PolicyNumber" value="{{ old('PolicyNumber', $record->PolicyNumber) }}" required>
        </div>

        <div class="mb-3">
            <label for="InsuranceProvider" class="form-label">Insurance Provider</label>
            <select class="form-select" name="InsuranceProvider" required>
                <option value="">Select Provider</option>
                @foreach ($insuranceProvider as $provider)
                    <option value="{{ $provider->Id }}" {{ old('InsuranceProvider', $record->InsuranceProvider) == $provider->Id ? 'selected' : '' }}>
                        {{ $provider->Name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="PremiumAmount" class="form-label">Premium Amount</label>
            <input type="number" step="0.01" class="form-control" name="PremiumAmount" value="{{ old('PremiumAmount', $record->PremiumAmount) }}" required>
        </div>

        <div class="mb-3">
            <label for="CoverageStartDate" class="form-label">Start Date</label>
            <input type="date" class="form-control" name="CoverageStartDate" value="{{ old('CoverageStartDate', $record->CoverageStartDate) }}" required>
        </div>

        <div class="mb-3">
            <label for="CoverageEndDate" class="form-label">Expiry Date</label>
            <input type="date" class="form-control" name="CoverageEndDate" value="{{ old('CoverageEndDate', $record->CoverageEndDate) }}" required>
        </div>

        <div class="mb-3">
            <label for="RenewalReminderDate" class="form-label">Renewal Reminder Date</label>
            <input type="date" class="form-control" name="RenewalReminderDate" value="{{ old('RenewalReminderDate', $record->RenewalReminderDate) }}" required>
        </div>

        <div class="mb-3">
            <label for="Status" class="form-label">Status</label>
            <select class="form-select" name="Status" required>
                <option value="">Select Type</option>
                @foreach ($insuranceStatuses as $status)
                    <option value="{{ $status->ID }}" {{ old('Status', $record->Status) == $status->ID ? 'selected' : '' }}>
                        {{ $status->Description }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="Notes" class="form-label">Notes</label>
            <textarea name="Notes" class="form-control" rows="3">{{ old('Notes', $record->Notes) }}</textarea>
        </div>

        <div class="mb-3">
            <label for="DocumentPath" class="form-label">Upload Document</label>
            <input type="file" class="form-control" name="DocumentPath" accept=".pdf,.doc,.docx,.xls,.xlsx">
            <small class="text-muted">Max size: 5MB. Allowed formats: PDF, DOC, DOCX, XLS, XLSX</small>
            @if($record->DocumentPath)
                <div class="mt-2">
                    <a href="{{ Storage::url($record->DocumentPath) }}" target="_blank">📄 View Existing Document</a>
                </div>
            @endif
        </div>

        <button type="submit" class="btn btn-success">💾 Update Insurance Record</button>
    </form>
</div>
@endsection
