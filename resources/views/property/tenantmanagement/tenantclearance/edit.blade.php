@extends('layouts.app')
@section('title', 'Property Tenant Clearance')
@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <h1>Edit Tenant Clearance</h1>
    <form action="{{ route('tenantclearance.update', $clearancetenant->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="Tenant" class="form-label">Tenant Name:</label>
            <select name="Tenant" class="form-select" required>
              <option>--Select the tenant</option>
                @foreach ($newtenants as $newtenant)
                    <option value="{{ $newtenant->Id }}" {{ $clearancetenant->Tenant == $newtenant->Id ? 'selected' : '' }}>
                        {{ $newtenant->TenantName }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="ExitDate" class="form-label">Exit Date:</label>
            <input type="date" name="ExitDate" class="form-control"
                   value="{{ old('ExitDate', $clearancetenant->ExitDate) }}" required>
        </div>

        <div class="mb-3">
            <label for="FinalInspection" class="form-label">Final Inspection Done?</label>
            <select name="FinalInspection" class="form-select" required>
                <option value="1" {{ $clearancetenant->FinalInspection ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ !$clearancetenant->FinalInspection ? 'selected' : '' }}>No</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="AllDuesPaid" class="form-label">All Dues Paid?</label>
            <select name="AllDuesPaid" class="form-select" required>
                <option value="1" {{ $clearancetenant->AllDuesPaid ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ !$clearancetenant->AllDuesPaid ? 'selected' : '' }}>No</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="KeysReturned" class="form-label">Keys Returned?</label>
            <select name="KeysReturned" class="form-select" required>
                <option value="1" {{ $clearancetenant->KeysReturned ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ !$clearancetenant->KeysReturned ? 'selected' : '' }}>No</option>
            </select>
        </div>  
        <div class="mb-3">
            <label for="DepositRefunded" class="form-label">Deposit Status:</label>
            <select name="DepositRefunded" class="form-select" required>
                <option value="">--Select Deposit Status--</option>
                @foreach ($codedetails as $codedetail)
                    <option value="{{ $codedetail->ID }}" {{ $clearancetenant->DepositRefunded == $codedetail->ID ? 'selected' : '' }}>
                        {{ $codedetail->Description }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="AdditionalNotes" class="form-label">Additional Notes (optional):</label>
            <textarea name="AdditionalNotes" class="form-control"
                      rows="4">{{ old('AdditionalNotes', $clearancetenant->AdditionalNotes) }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Update Tenant Clearance</button>
        <a href="{{ route('tenantclearance.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
@endsection
