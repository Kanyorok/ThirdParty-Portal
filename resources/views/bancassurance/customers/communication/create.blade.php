@extends('layouts.app')

@section('title', 'Log Communication')

@section('content')
<div class="container my-4" style="max-width: 900px;">
    <form method="POST" action="{{ route('bancassurance.customers.communication.store') }}">
        @csrf

        <div class="card shadow border-0 rounded-4 overflow-hidden">
            <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-normal">
                    <i class="bi bi-chat-dots-fill me-2"></i> New Communication
                </h5>
                <a href="{{ route('bancassurance.customers.communication.index') }}" class="btn btn-light btn-sm rounded-pill px-3 fw-semibold">
                    <i class="bi bi-arrow-left-circle me-1"></i> Back
                </a>
            </div>

            <div class="card-body p-4">

                {{-- Customer Information --}}
                <section class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="CustomerID" class="form-label fw-medium text-muted">
                                Customer <span class="text-danger">*</span>
                            </label>
                            <select name="CustomerID" id="CustomerID"
                                class="form-select form-select-sm rounded-pill shadow-sm" required>
                                <option value="">-- Select Customer --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->Id }}">
                                        {{ $customer->thirdParty->ThirdPartyName ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="HandledBy" class="form-label fw-medium text-muted">
                                Handled By <span class="text-danger">*</span>
                            </label>
                            <select name="HandledBy" id="HandledBy"
                                class="form-select form-select-sm rounded-pill shadow-sm" required>
                                <option value="">-- Select Employee --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->Id }}">
                                        {{ $emp->FirstName }} {{ $emp->LastName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </section>

                {{-- Communication Details --}}
                <section class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="ContactDate" class="form-label fw-medium text-muted">
                                Contact Date <span class="text-danger">*</span>
                            </label>
                            <input type="datetime-local" name="ContactDate" id="ContactDate"
                                class="form-control form-control-sm rounded-pill shadow-sm" required>
                        </div>

                        <div class="col-md-4">
                            <label for="ContactType" class="form-label fw-medium text-muted">
                                Contact Type <span class="text-danger">*</span>
                            </label>
                            <select name="ContactType" id="ContactType"
                                class="form-select form-select-sm rounded-pill shadow-sm" required>
                                <option value="">-- Select Contact Type --</option>
                                @foreach ($contacttypes as $contacttype)
                                    <option value="{{ $contacttype->ID }}">
                                        {{ $contacttype->Description ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="Summary" class="form-label fw-medium text-muted">
                                Summary <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="Summary" id="Summary"
                                class="form-control form-control-sm rounded-pill shadow-sm"
                                placeholder="Brief summary..." required>
                        </div>
                    </div>
                </section>

                {{-- Additional Notes --}}
                <section>
                    <textarea name="Notes" class="form-control form-control-sm rounded-3 shadow-sm"
                        rows="3" placeholder="Add more details or remarks..."></textarea>
                </section>

            </div>

            <div class="card-footer d-flex justify-content-between align-items-center bg-light py-3 px-4">
                <a href="{{ route('bancassurance.customers.communication.index') }}"
                    class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-arrow-left-circle me-2"></i> Cancel
                </a>
                <button type="submit" class="btn btn-success rounded-pill px-5 fw-semibold"
                    onclick="this.disabled=true; this.innerHTML='<i class=\'bi bi-arrow-repeat me-2 spin\'></i> Submitting...'; this.form.submit();">
                    <i class="bi bi-save me-2"></i> Save Communication
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Optional small CSS spinner tweak --}}
@push('styles')
<style>
    .spin { animation: spin 1s linear infinite; }
    @keyframes spin { 100% { transform: rotate(360deg); } }
</style>
@endpush
@endsection
