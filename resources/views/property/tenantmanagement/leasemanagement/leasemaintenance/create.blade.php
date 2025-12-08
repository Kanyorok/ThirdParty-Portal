@extends('layouts.app')

@section('title', 'New Lease Agreement')

@section('content')

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong class="d-block mb-2">Please fix the following errors:</strong>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="container mt-4">
        <form method="POST" action="{{ route('addlease.store') }}" enctype="multipart/form-data" id="leaseForm">
            @csrf

            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white fw-bold py-3">Lease Details</div>
                <div class="card-body">

                    {{-- Tenant & Property --}}
                    <h5 class="fw-bold border-bottom pb-2 mb-3">Tenant & Property Information</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Select Tenant <span class="text-danger">*</span></label>
                            <select name="Tenant" class="form-select shadow-sm" required>
                                <option value="">-- Select Tenant --</option>
                                @foreach ($newtenants as $newtenant)
                                    <option value="{{ $newtenant->Id }}">
                                        {{ $newtenant->thirdParty->ThirdPartyName ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Select Property <span class="text-danger">*</span></label>
                            <select name="PropertyID" id="property-select" class="form-select shadow-sm" required>
                                <option value="">-- Select Property --</option>
                                @foreach ($properties as $property)
                                    <option value="{{ $property->Id }}">
                                        {{ $property->PropertyName ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Property Hierarchy --}}
                    <h5 class="fw-bold border-bottom pb-2 mb-3">Block / Floor / Unit</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Select Block <span class="text-danger">*</span></label>
                            <select name="BlockID" id="block-select" class="form-select shadow-sm" required>
                                <option value="">-- Select Block --</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Select Floor <span class="text-danger">*</span></label>
                            <select name="FloorID" id="floor-select" class="form-select shadow-sm" required>
                                <option value="">-- Select Floor --</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Select Unit <span class="text-danger">*</span></label>
                            <select name="Unit" id="unit-select" class="form-select shadow-sm" required>
                                <option value="">-- Select Unit --</option>
                            </select>
                        </div>
                    </div>

                    {{-- Lease Duration --}}
                    <h5 class="fw-bold border-bottom pb-2 mb-3">Lease Duration</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control shadow-sm" name="StartDate" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control shadow-sm" name="EndDate" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Payment Frequency <span class="text-danger">*</span></label>
                            <select class="form-select shadow-sm" name="PaymentFrequency" required>
                                <option value="">-- Select Frequency --</option>
                                @foreach ($codes as $code)
                                    <option value="{{ $code->ID }}">{{ $code->Description }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Financials --}}
                    <h5 class="fw-bold border-bottom pb-2 mb-3">Financial Details</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Rent <span class="text-danger">*</span></label>
                            <input type="number" class="form-control shadow-sm charge-field" name="MonthlyRent" placeholder="e.g. 25000" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Deposit <span class="text-danger">*</span></label>
                            <input type="number" class="form-control shadow-sm" name="Deposit" placeholder="e.g. 25000" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Service Charge <span class="text-danger">*</span></label>
                            <input type="number" class="form-control shadow-sm charge-field" name="ServiceCharge" placeholder="e.g. 5000" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Parking Fee <span class="text-danger">*</span></label>
                            <input type="number" class="form-control shadow-sm charge-field" name="ParkingFee" placeholder="e.g. 1000" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Other Charges <span class="text-danger">*</span></label>
                            <input type="number" class="form-control shadow-sm charge-field" name="OtherCharges" placeholder="e.g. 250" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Total Payable</label>
                            <input type="number" class="form-control shadow-sm" id="TotalPayable" readonly>
                        </div>
                    </div>

                    {{-- Due Day --}}
                    <h5 class="fw-bold border-bottom pb-2 mb-3">Payment Due Date</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Due Day <span class="text-danger">*</span></label>
                            <input type="number" class="form-control shadow-sm" name="DueDay" placeholder="1 - 28" min="1" max="28" required>
                            <small class="text-muted">Must be between 1 and 28</small>
                        </div>
                    </div>

                    {{-- Terms --}}
                    <h5 class="fw-bold border-bottom pb-2 mb-3">Special Terms</h5>
                    <div class="mb-4">
                        <textarea class="form-control shadow-sm" rows="3" name="SpecialTerms" placeholder="Optional terms or notes..."></textarea>
                    </div>

                    {{-- Document Upload --}}
                    <h5 class="fw-bold border-bottom pb-2 mb-3">Upload Documents</h5>
                    <div class="mb-4">
                        <label class="form-label">Lease Documents <span class="text-danger">*</span></label>
                        <input type="file" name="Document[]" class="form-control shadow-sm" accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx" multiple required>
                        <small class="text-muted">Allowed types: pdf, images, docx, xlsx | Max: 25MB</small>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="d-flex gap-3 justify-content-end">
                        <a href="{{ route('addlease.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-success px-4" onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
                            Save Lease
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </div>

    {{-- Scripts --}}
    <script>
        const routes = {
            getBlocks: "{{ route('getblockbyproperty.lease', ['PropertyId' => '__ID__']) }}",
            getFloors: "{{ route('getfloorbyblock.lease', ['BlockId' => '__ID__']) }}",
            getUnits: "{{ route('getunitbyfloor.lease', ['FloorId' => '__ID__']) }}",
            getPricing: "{{ route('getpricingunit.lease', ['UnitId' => '__ID__']) }}"
        };

        document.addEventListener('DOMContentLoaded', () => {
            const propertySelect = document.getElementById('property-select');
            const blockSelect = document.getElementById('block-select');
            const floorSelect = document.getElementById('floor-select');
            const unitSelect = document.getElementById('unit-select');
            const totalField = document.getElementById('TotalPayable');
            const chargeFields = document.querySelectorAll('.charge-field');

            const rentInput = document.querySelector('input[name="MonthlyRent"]');
            const depositInput = document.querySelector('input[name="Deposit"]');
            const serviceInput = document.querySelector('input[name="ServiceCharge"]');
            const parkingInput = document.querySelector('input[name="ParkingFee"]');
            const otherInput = document.querySelector('input[name="OtherCharges"]');

            const resetOptions = (select, label) => {
                select.innerHTML = `<option value="">-- ${label} --</option>`;
            };

            propertySelect.addEventListener('change', function () {
                resetOptions(blockSelect, 'Select Block');
                resetOptions(floorSelect, 'Select Floor');
                resetOptions(unitSelect, 'Select Unit');

                if (this.value) {
                    fetch(routes.getBlocks.replace('__ID__', this.value))
                        .then(res => res.json())
                        .then(blocks => {
                            blocks.forEach(b => {
                                blockSelect.insertAdjacentHTML('beforeend', `<option value="${b.Id}">${b.BlockName}</option>`);
                            });
                        });
                }
            });

            blockSelect.addEventListener('change', function () {
                resetOptions(floorSelect, 'Select Floor');
                resetOptions(unitSelect, 'Select Unit');

                if (this.value) {
                    fetch(routes.getFloors.replace('__ID__', this.value))
                        .then(res => res.json())
                        .then(floors => {
                            floors.forEach(f => {
                                floorSelect.insertAdjacentHTML('beforeend', `<option value="${f.Id}">${f.FloorLabel}</option>`);
                            });
                        });
                }
            });

            floorSelect.addEventListener('change', function () {
                resetOptions(unitSelect, 'Select Unit');

                if (this.value) {
                    fetch(routes.getUnits.replace('__ID__', this.value))
                        .then(res => res.json())
                        .then(units => {
                            units.forEach(u => {
                                unitSelect.insertAdjacentHTML('beforeend', `<option value="${u.Id}">${u.UnitCode}</option>`);
                            });
                        });
                }
            });

            unitSelect.addEventListener('change', function () {
                if (!this.value) return;

                fetch(routes.getPricing.replace('__ID__', this.value))
                    .then(res => res.json())
                    .then(p => {
                        if (!p) return;

                        rentInput.value = p.Rent ?? '';
                        depositInput.value = p.DepositAmount ?? '';
                        serviceInput.value = p.ServiceCharge ?? '';
                        parkingInput.value = p.ParkingFee ?? '';
                        otherInput.value = p.OtherCharges ?? '';

                        calculateTotal();
                    });
            });

            const calculateTotal = () => {
                let total = 0;
                chargeFields.forEach(i => total += parseFloat(i.value) || 0);
                totalField.value = total;
            };

            chargeFields.forEach(i => i.addEventListener('input', calculateTotal));
        });
    </script>
@endsection