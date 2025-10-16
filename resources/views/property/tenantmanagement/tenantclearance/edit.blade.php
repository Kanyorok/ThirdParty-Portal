@php use Carbon\Carbon; @endphp
@php use App\Enums\Property\TenantClearanceEnum; @endphp
@extends('layouts.app')

@section('title', 'Edit Tenant Clearance')

@section('content')
    <div class="container mt-5" style="max-width: 800px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
        </div>

        <form method="POST" action="{{ route('tenantclearance.update', $clearancetenant->Id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    {{-- Tenant Info --}}
                    <div class="mb-3">
                        <label class="form-label">Tenant && Lease Info:</label> <br>
                        <label class="form-label">
                            Tenant: {{ $clearancetenant->lease->tenant->thirdParty->TradingName  ?? '-' }}
                            &nbsp;&nbsp; Lease No: {{ $clearancetenant->lease->LeaseNumber ?? '-' }}
                        </label>
                        <input type="hidden" name="LeaseId" value="{{ $clearancetenant->LeaseId }}">
                    </div>


                    {{-- Exit Date --}}
                    <div class="mb-3">
                        <label class="form-label">Exit Date</label>
                        <input type="text" id="exit-date" name="ExitDate" class="form-control"
                               value="{{ old('ExitDate', Carbon::parse($clearancetenant->ExitDate)->format('d/m/Y')) }}"
                               required>
                    </div>

                    {{-- Final Inspection, Dues Cleared, Keys Returned (one row) --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Final Inspection Done</label>
                            <select name="FinalInspection" class="form-select" required>
                                <option
                                    value="1" {{ old('FinalInspection', $clearancetenant->FinalInspection) == 1 ? 'selected' : '' }}>
                                    Yes
                                </option>
                                <option
                                    value="0" {{ old('FinalInspection', $clearancetenant->FinalInspection) == 0 ? 'selected' : '' }}>
                                    No
                                </option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Dues Cleared</label>
                            <select name="AllDuesPaid" class="form-select" required>
                                <option
                                    value="1" {{ old('AllDuesPaid', $clearancetenant->AllDuesPaid) == 1 ? 'selected' : '' }}>
                                    Yes
                                </option>
                                <option
                                    value="0" {{ old('AllDuesPaid', $clearancetenant->AllDuesPaid) == 0 ? 'selected' : '' }}>
                                    No
                                </option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Keys Returned</label>
                            <select name="KeysReturned" class="form-select" required>
                                <option
                                    value="1" {{ old('KeysReturned', $clearancetenant->KeysReturned) == 1 ? 'selected' : '' }}>
                                    Yes
                                </option>
                                <option
                                    value="0" {{ old('KeysReturned', $clearancetenant->KeysReturned) == 0 ? 'selected' : '' }}>
                                    No
                                </option>
                            </select>
                        </div>
                    </div>

                    {{-- Deposit Refunded --}}
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

                    {{-- Additional Notes --}}
                    <div class="mb-3">
                        <label class="form-label">Additional Notes</label>
                        <textarea name="AdditionalNotes" class="form-control"
                                  rows="3">{{ old('AdditionalNotes', $clearancetenant->AdditionalNotes) }}</textarea>
                    </div>

                    {{-- Status --}}
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="Status" class="form-select" required>
                            @foreach (TenantClearanceEnum::cases() as $status)
                                <option value="{{ $status->value }}"
                                    {{ old('Status', $clearancetenant->Status->value) == $status->value ? 'selected' : '' }}>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                {{-- Form Actions --}}
                <div class="card-footer bg-light d-flex justify-content-between">
                    <a href="{{ route('tenantclearance.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-success">Save Changes</button>
                </div>
            </div>
        </form>
    </div>

    {{-- Date Picker --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("#exit-date", {
            dateFormat: "d/m/Y",
            allowInput: true
        });
    </script>
@endsection
