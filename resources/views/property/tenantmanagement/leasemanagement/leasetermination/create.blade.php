@php use Carbon\Carbon; @endphp
@extends('layouts.app')
@section('title', 'Terminate Lease')

@section('content')
<div class="container mt-4">

    <form action="{{ route('terminatelease.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card shadow">
            <div class="card-header bg-light fw-bold">Termination Details</div>
            <div class="card-body">

                <!-- Lease Selection -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Select Lease <span class="text-danger">*</span></label>
                        <select name="LeaseID"
                                class="form-select @error('LeaseID') is-invalid @enderror"
                                required>

                            <option value="">-- Select Lease --</option>

                            @foreach ($newtenants as $newtenant)
                                <option value="{{ $newtenant->Id }}"
                                    {{ old('LeaseID') == $newtenant->Id ? 'selected' : '' }}>
                                    LSno: {{ $newtenant->LeaseNumber ?? 'No.'}} —
                                    Name: {{ $newtenant->tenant->thirdParty->ThirdPartyName ?? 'Name'}}
                                </option>
                            @endforeach
                        </select>

                        @error('LeaseID')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Termination Date -->
                    <div class="col-md-6">
                        <label class="form-label">Termination Date <span class="text-danger">*</span></label>
                        <input type="date"
                               name="TerminationDate"
                               class="form-control @error('TerminationDate') is-invalid @enderror"
                               value="{{ old('TerminationDate', Carbon::now()->format('Y-m-d')) }}"
                               required>

                        @error('TerminationDate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Termination Reason & Remarks -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Reason for Termination <span class="text-danger">*</span></label>

                        <select name="TerminationReason"
                                class="form-select @error('TerminationReason') is-invalid @enderror"
                                required>
                            <option value="">-- Select Reason --</option>

                            @foreach ($terminationReasons as $reason)
                                <option value="{{ $reason->ID }}"
                                    {{ old('TerminationReason') == $reason->ID ? 'selected' : '' }}>
                                    {{ $reason->Description ?? 'No Description' }}
                                </option>
                            @endforeach
                        </select>

                        @error('TerminationReason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Remarks</label>
                        <input type="text"
                               name="Remarks"
                               class="form-control @error('Remarks') is-invalid @enderror"
                               value="{{ old('Remarks') }}"
                               placeholder="e.g. Cleared & handed back keys">

                        @error('Remarks')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Document Upload -->
                <div class="mb-3">
                    <label class="form-label">Upload Supporting Documents <span class="text-danger">*</span></label>

                    <input type="file"
                           name="Document[]"
                           class="form-control @error('Document') is-invalid @enderror @error('Document.*') is-invalid @enderror"
                           accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx"
                           multiple
                           required>

                    <small class="text-muted d-block mt-1 mb-2">
                        Allowed: .pdf, .jpg, .jpeg, .png, .docx, .xlsx | Max size: 25MB each
                    </small>

                    @error('Document')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    @error('Document.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('terminatelease.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit"
                            class="btn btn-success"
                            onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
                        Terminate Lease
                    </button>
                </div>

            </div>
        </div>
    </form>

</div>
@endsection
