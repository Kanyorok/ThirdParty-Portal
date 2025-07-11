@extends('layouts.app')

@section('title', 'Edit Tenant Clearance')

@section('content')
<div class="container mt-5" style="max-width: 800px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">📝 Edit Tenant Clearance</h3>
        <a href="{{ route('tenantclearance.index') }}" class="btn btn-outline-secondary btn-sm">← Back to List</a>
    </div>

    <form method="POST" action="{{ route('tenantclearance.update', $clearancetenant->Id) }}">
    @csrf
    @method('PUT')

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="mb-3">
                <label class="form-label">Tenant Name: {{ $clearancetenant->lease->tenant->TenantName ?? 'N/A' }}</label>
                <input type="hidden" name="LeaseId" value="{{ $clearancetenant->LeaseId }}">
            </div>

            <input type="text" id="exit-date" name="ExitDate" class="form-control"
                value="{{ old('ExitDate', \Carbon\Carbon::parse($clearancetenant->ExitDate)->format('d/m/Y')) }}" required>
                        
            <div class="mb-3">
                <label class="form-label">Final Inspection Completed</label>
                <select name="FinalInspection" class="form-select" required>
                    <option value="1" {{ old('FinalInspection', $clearancetenant->FinalInspection) == 1 ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ old('FinalInspection', $clearancetenant->FinalInspection) == 0 ? 'selected' : '' }}>No</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">All Dues Paid</label>
                <select name="AllDuesPaid" class="form-select" required>
                    <option value="1" {{ old('AllDuesPaid', $clearancetenant->AllDuesPaid) == 1 ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ old('AllDuesPaid', $clearancetenant->AllDuesPaid) == 0 ? 'selected' : '' }}>No</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Keys Returned</label>
                <select name="KeysReturned" class="form-select" required>
                    <option value="1" {{ old('KeysReturned', $clearancetenant->KeysReturned) == 1 ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ old('KeysReturned', $clearancetenant->KeysReturned) == 0 ? 'selected' : '' }}>No</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Deposit Refunded</label>
                <select name="DepositRefunded" class="form-select" required>
                    @foreach ($codedetails as $code)
                        <option value="{{ $code->ID }}"
                            {{ old('DepositRefunded', $clearancetenant->DepositRefunded) == $code->ID ? 'selected' : '' }}>
                            {{ $code->Description }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Additional Notes</label>
                <textarea name="AdditionalNotes" class="form-control" rows="3">{{ old('AdditionalNotes', $clearancetenant->AdditionalNotes) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="Status" class="form-select" required>
                    @foreach (\App\Enums\Property\TenantClearanceEnum::cases() as $status)
                        <option value="{{ $status->value }}"
                            {{ old('Status', $clearancetenant->Status) == $status->value ? 'selected' : '' }}>
                            {{ $status->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="card-footer bg-light d-flex justify-content-between">
            <button type="submit" class="btn btn-success">Save Changes</button>
            <a href="{{ route('tenantclearance.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </div>
</form>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    flatpickr("#exit-date", {
        dateFormat: "d/m/Y",
        allowInput: true
    });
</script>
@endsection
