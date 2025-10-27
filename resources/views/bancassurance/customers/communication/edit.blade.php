@extends('layouts.app')

@section('title', 'Communication Log')

@section('content')
<div class="container my-4" style="max-width: 900px;">
    <form method="POST" action="{{ route('bancassurance.customers.communication.update', $log->Id) }}">
        @csrf
        @method('PUT')

        <div class="card shadow border-0 rounded-4 overflow-hidden">
            <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-normal">
                    <i class="bi bi-chat-square-text-fill me-2"></i> Edit Communication Log
                </h5>
                <a href="{{ route('bancassurance.customers.communication.index') }}" 
                   class="btn btn-light btn-sm rounded-pill px-3 fw-semibold">
                    <i class="bi bi-arrow-left-circle me-1"></i> Back
                </a>
            </div>

            <div class="card-body p-4">

                {{-- Customer Information --}}
                <section class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted">Customer</label>
                            <input type="text" class="form-control form-control-sm rounded-pill shadow-sm"
                                   value="{{ $log->customers->thirdParty->ThirdPartyName }}" disabled>
                        </div>
                    </div>
                </section>
                
                <section class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-medium text-muted">Contact Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="ContactDate"
                                   class="form-control form-control-sm rounded-pill shadow-sm"
                                   value="{{ \Carbon\Carbon::parse($log->ContactDate)->format('Y-m-d\TH:i') }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-medium text-muted">Contact Type <span class="text-danger">*</span></label>
                            <select name="ContactType" class="form-select form-select-sm rounded-pill shadow-sm" required>
                                <option value="">-- Select Contact Type --</option>
                                @foreach ($contacttypes as $contacttype)
                                    <option value="{{ $contacttype->ID }}"
                                        {{ $log->ContactType == $contacttype->ID ? 'selected' : '' }}>
                                        {{ $contacttype->Description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-medium text-muted">Handled By</label>
                            <select name="HandledBy" class="form-select form-select-sm rounded-pill shadow-sm">
                                <option value="">-- Select Handler --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->Id }}" {{ $log->HandledBy == $emp->Id ? 'selected' : '' }}>
                                        {{ $emp->FirstName }} {{ $emp->LastName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </section>

                <section>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted">Summary <span class="text-danger">*</span></label>
                            <input type="text" name="Summary"
                                   class="form-control form-control-sm rounded-pill shadow-sm"
                                   value="{{ old('Summary', $log->Summary) }}" placeholder="Brief summary..." required>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-medium text-muted">Detailed Notes</label>
                            <textarea name="Notes" class="form-control form-control-sm rounded-3 shadow-sm"
                                      rows="4" placeholder="Add more details or remarks...">{{ old('Notes', $log->Notes) }}</textarea>
                        </div>
                    </div>
                </section>

            </div>

            <div class="card-footer d-flex justify-content-between align-items-center bg-light py-3 px-4">
                <a href="{{ route('bancassurance.customers.communication.index') }}"
                   class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-arrow-left-circle me-2"></i> Cancel
                </a>
                <button type="submit" class="btn btn-success rounded-pill px-5 fw-semibold"
                        onclick="this.disabled=true; this.innerHTML='<i class=\'bi bi-arrow-repeat me-2 spin\'></i> Updating...'; this.form.submit();">
                    <i class="bi bi-save me-2"></i> Update Log
                </button>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
    .spin { animation: spin 1s linear infinite; }
    @keyframes spin { 100% { transform: rotate(360deg); } }
</style>
@endpush
@endsection
