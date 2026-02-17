@extends('layouts.app')
@section('title', 'Renew Lease Agreement')

@section('content')
    {{-- Error Messages --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Flash Messages --}}
    @if (session('error'))
        <script>alert("{{ session('error') }}");</script>
    @endif

    @if (session('success'))
        <script>alert("{{ session('success') }}");</script>
    @endif

    <div class="container mt-4">
        <form action="{{ route('renewlease.store') }}" method="POST">
            @csrf
            <div class="card shadow">
                <div class="card-header bg-primary fw-bold">New Lease Terms</div>
                <div class="card-body">

                    {{-- Lease Selection --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Select Lease Number<span class="text-danger">*</span></label>
                            <select id="lease-select" name="LeaseId" class="form-select" required>
                                <option value="">-- Select Lease --</option>
                                @foreach ($newleases as $lease)
                                    <option value="{{ $lease->Id }}"
                                            data-leasenumber="{{ $lease->LeaseNumber ?? 'No.' }}"
                                            data-tenant-id="{{ $lease->Tenant }}"
                                            data-tenant-name="{{ $lease->tenant->thirdParty->ThirdPartyName ?? 'N/A' }}"
                                            data-property-id="{{ $lease->PropertyID }}"
                                            data-property-name="{{ $lease->property->PropertyName ?? 'N/A' }}"
                                            data-frequency-id="{{ $lease->PaymentFrequency }}"
                                            data-frequency-name="{{ $lease->code->Description ?? 'N/A' }}"
                                            data-end-date="{{ $lease->EndDate ? \Carbon\Carbon::parse($lease->EndDate)->format('Y-m-d') : '' }}"
                                            data-rent="{{ $lease->MonthlyRent ?? '0' }}"
                                            data-service="{{ $lease->ServiceCharge ?? '0'}}"
                                            data-parking="{{ $lease->ParkingFee ?? '0' }}"
                                            data-other="{{ $lease->OtherCharges ?? '0' }}">
                                        {{ $lease->LeaseNumber }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Lease Number<span class="text-danger">*</span></label>
                            <input type="text" id="lease-display" class="form-control" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tenant<span class="text-danger">*</span></label>
                            <input type="text" id="tenant-display" class="form-control" readonly>
                            <input type="hidden" name="TenantId" id="tenant-id">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Property<span class="text-danger">*</span></label>
                            <input type="text" id="property-display" class="form-control" readonly>
                            <input type="hidden" name="PropertyId" id="property-id">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Payment Frequency<span class="text-danger">*</span></label>
                            <input type="text" id="frequency-display" class="form-control" readonly>
                            <input type="hidden" name="PaymentFrequency" id="frequency-id">
                        </div>
                    </div>

                    {{-- Dates --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">End Date of Current Lease<span class="text-danger">*</span></label>
                            <input type="test" id="enddate-current" class="form-control" readonly
                                   name="EndDateCurrentLease"
                                   value="{{ old('EndDateCurrentLease') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">New Start Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control"
                                   name="NewStartDate"
                                   value="{{ old('NewStartDate') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">New End Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control"
                                   name="NewEndDate"
                                   value="{{ old('NewEndDate') }}">
                        </div>
                    </div>

                    {{-- Financial Terms --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Rent <span class="text-danger">*</span></label>
                            <input type="number" id="rent" class="form-control"
                                   name="NewMonthlyRent"
                                   value="{{ old('NewMonthlyRent') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Service Charge<span class="text-danger">*</span></label>
                            <input type="number" id="service" class="form-control"
                                   name="ServiceCharge"
                                   value="{{ old('ServiceCharge') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Parking Fee<span class="text-danger">*</span></label>
                            <input type="number" id="parking" class="form-control"
                                   name="ParkingFee"
                                   value="{{ old('ParkingFee') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Other Charges<span class="text-danger">*</span></label>
                            <input type="number" id="other" class="form-control"
                                   name="OtherCharges"
                                   value="{{ old('OtherCharges') }}">
                        </div>
                    </div>

                    {{-- Total --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Total</label>
                            <input type="number" id="total-display" class="form-control bg-light fw-bold" readonly>
                        </div>
                    </div>

                    {{-- Remarks --}}
                    <div class="mb-3">
                        <label class="form-label">Remarks or Changes <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="Remarks" rows="2">{{ old('Remarks') }}</textarea>
                    </div>

                    {{-- Submit --}}
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('renewlease.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success"
                                onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
                            Renew Lease
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Auto-fill Script --}}
    <script>
        const leaseSelect = document.getElementById('lease-select');

        function populateFromOption(selected) {

            if (!selected || !selected.value) {
                ['lease-display', 'tenant-display', 'property-display', 'frequency-display', 'enddate-current', 'rent', 'service', 'parking', 'other'].forEach(id => {
                    const el = document.getElementById(id);
                    if (!el) return;
                    if (el.type === 'number') el.value = 0; else el.value = '';
                });
                ['tenant-id', 'property-id', 'frequency-id'].forEach(hid => {
                    const h = document.getElementById(hid);
                    if (h) h.value = '';
                });
                calculateTotal();
                return;
            }

            // Visible fields
            document.getElementById('lease-display').value = selected.dataset.leasenumber || '';
            document.getElementById('tenant-display').value = selected.dataset.tenantName || '';
            document.getElementById('tenant-id').value = selected.dataset.tenantId || '';
            document.getElementById('property-display').value = selected.dataset.propertyName || '';
            document.getElementById('property-id').value = selected.dataset.propertyId || '';
            document.getElementById('frequency-display').value = selected.dataset.frequencyName || '';
            document.getElementById('frequency-id').value = selected.dataset.frequencyId || '';

            // ✅ Robust End Date logic
            const endDate =
                selected.dataset.endDate ||
                selected.getAttribute('data-end-date') ||
                selected.dataset.enddate ||
                selected.getAttribute('data-enddate') || '';

            document.getElementById('enddate-current').value = endDate || '';

            // Financials
            document.getElementById('rent').value = selected.dataset.rent || 0;
            document.getElementById('service').value = selected.dataset.service || 0;
            document.getElementById('parking').value = selected.dataset.parking || 0;
            document.getElementById('other').value = selected.dataset.other || 0;

            calculateTotal();
        }

        function calculateTotal() {
            let rent = parseFloat(document.getElementById('rent').value) || 0;
            let service = parseFloat(document.getElementById('service').value) || 0;
            let parking = parseFloat(document.getElementById('parking').value) || 0;
            let other = parseFloat(document.getElementById('other').value) || 0;
            document.getElementById('total-display').value = rent + service + parking + other;
        }

        if (leaseSelect) {
            leaseSelect.addEventListener('change', function () {
                populateFromOption(this.options[this.selectedIndex]);
            });
        }

        ['rent', 'service', 'parking', 'other'].forEach(id => {
            document.getElementById(id).addEventListener('input', calculateTotal);
        });

        window.addEventListener('load', function () {
            if (leaseSelect && leaseSelect.value) {
                populateFromOption(leaseSelect.options[leaseSelect.selectedIndex]);
            }
            calculateTotal();
        });
    </script>
@endsection
