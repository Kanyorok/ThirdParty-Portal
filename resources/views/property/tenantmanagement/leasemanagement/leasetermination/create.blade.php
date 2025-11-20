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
                            <label class="form-label">Select Lease<span class="text-danger">*</span></label>
                            <select name="LeaseID" class="form-select" required>
                                <option value="">-- Select Lease --</option>
                                @foreach ($newtenants as $newtenant)
                                    <option value="{{ $newtenant->Id }}">
                                        LSno: {{ $newtenant->LeaseNumber }} —
                                        Name: {{ $newtenant->tenant->thirdParty->ThirdPartyName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Termination Date -->
                        <div class="col-md-6">
                            <label class="form-label">Termination Date<span class="text-danger">*</span></label>
                            <input type="date" name="TerminationDate"
                                   class="form-control @error('TerminationDate') is-invalid @enderror"
                                   value="{{ old('TerminationDate', Carbon::now()->format('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <!-- Termination Reason & Remarks -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Reason for Termination<span class="text-danger">*</span></label>
                            <select name="TerminationReason" class="form-select" required>
                                <option value="">-- Select Reason --</option>
                                @foreach ($terminationReasons as $reason)
                                    <option value="{{ $reason->ID }}">{{ $reason->Description }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="Remarks"
                                   class="form-control @error('Remarks') is-invalid @enderror"
                                   value="{{ old('Remarks') }}" placeholder="e.g. Cleared & handed back keys">
                            @error('Remarks')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Document Upload -->
                    <div class="mb-3">
                        <label class="form-label">Upload Supporting Documents<span class="text-danger">*</span></label>
                        <input type="file" name="Document[]" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx" multiple required>
                        <small class="text-muted d-block mb-1">Allowed file types: .pdf, .jpg, .jpeg, .png, .docx, .xlsx | Max size: 25MB</small>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-end">
                        <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();"> Terminate Lease</button>
                    </div>

                </div>
            </div>
        </form>
    </div>
@endsection
