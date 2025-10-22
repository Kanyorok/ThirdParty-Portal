@extends('layouts.app')

@section('title', 'New Lease Agreement')

@section('content')

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="container mt-4">
        <form method="POST" action="{{ route('addlease.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="card shadow">
                <div class="card-header bg-light fw-bold">Lease Details</div>
                <div class="card-body">

                    {{-- Tenant & Property --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Select Tenant <span class="text-danger">*</span></label>
                            <select name="Tenant" class="form-select" required>
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
                            <select name="PropertyID" id="property-select" class="form-select" required>
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
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Select Block <span class="text-danger">*</span></label>
                            <select name="BlockID" id="block-select" class="form-select" required>
                                <option value="">-- Select Block --</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Select Floor <span class="text-danger">*</span></label>
                            <select name="FloorID" id="floor-select" class="form-select" required>
                                <option value="">-- Select Floor --</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Select Unit <span class="text-danger">*</span></label>
                            <select name="Unit" id="unit-select" class="form-select" required>
                                <option value="">-- Select Unit --</option>
                            </select>
                        </div>
                    </div>

                    {{-- Lease Duration --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="StartDate" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="EndDate" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Payment Frequency <span class="text-danger">*</span></label>
                            <select class="form-select" name="PaymentFrequency" required>
                                <option value="">-- Select Frequency --</option>
                                @foreach ($codes as $code)
                                    <option value="{{ $code->ID }}">
                                        {{ $code->Description ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Financials --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Rent <span class="text-danger">*</span></label>
                            <input type="number" class="form-control charge-field" name="MonthlyRent"
                                   placeholder="e.g. 25000" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Deposit <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="Deposit" placeholder="e.g. 25000" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Service Charge <span class="text-danger">*</span></label>
                            <input type="number" class="form-control charge-field" name="ServiceCharge"
                                   placeholder="e.g. 5000" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Parking Fee <span class="text-danger">*</span></label>
                            <input type="number" class="form-control charge-field" name="ParkingFee"
                                   placeholder="e.g. 1000" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Other Charges <span class="text-danger">*</span></label>
                            <input type="number" class="form-control charge-field" name="OtherCharges"
                                   placeholder="e.g. 250" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Total Rent</label>
                            <input type="number" class="form-control" id="TotalPayable" readonly>
                        </div>
                    </div>

                    {{-- Due Day --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Due Date <span class="text-danger">*</span></label>
                            <input
                                type="number"
                                class="form-control"
                                name="DueDay"
                                placeholder="Due should be between 1 and 28"
                                min="1"
                                max="28"
                                required
                            >
                            <small class="text-muted">Must be between 1 and 28</small>
                        </div>
                    </div>

                    {{-- Terms --}}
                    <div class="mb-3">
                        <label class="form-label">Special Terms & Conditions</label>
                        <textarea
                            class="form-control"
                            rows="3"
                            name="SpecialTerms"
                            placeholder="Optional terms or notes..."
                        ></textarea>
                    </div>

                    {{-- Document Upload --}}
                    <div class="mb-3">
                        <label class="form-label">Upload Lease Document<span class="text-danger">*</span></label>
                        <input type="file" name="Document[]" class="form-control" multiple required>
                        <small class="text-muted">e.g. upload Lease Document</small>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="d-flex gap-2">
                        <a href="{{ route('addlease.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success"
                                onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
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
            getUnits: "{{ route('getunitbyfloor.lease', ['FloorId' => '__ID__']) }}"
        };

        document.addEventListener('DOMContentLoaded', () => {
            const propertySelect = document.getElementById('property-select');
            const blockSelect = document.getElementById('block-select');
            const floorSelect = document.getElementById('floor-select');
            const unitSelect = document.getElementById('unit-select');
            const totalField = document.getElementById('TotalPayable');
            const chargeFields = document.querySelectorAll('.charge-field');

            // Helper: reset options
            const resetOptions = (select, placeholder) => {
                select.innerHTML = `<option value="">-- ${placeholder} --</option>`;
            };

            // Property → Block
            propertySelect.addEventListener('change', function () {
                resetOptions(blockSelect, 'Select Block');
                resetOptions(floorSelect, 'Select Floor');
                resetOptions(unitSelect, 'Select Unit');

                if (this.value) {
                    fetch(routes.getBlocks.replace('__ID__', this.value))
                        .then(res => res.json())
                        .then(data => {
                            data.forEach(block => {
                                const option = document.createElement('option');
                                option.value = block.Id;
                                option.textContent = block.BlockName;
                                blockSelect.appendChild(option);
                            });
                        })
                        .catch(() => alert('Failed to load blocks.'));
                }
            });

            // Block → Floor
            blockSelect.addEventListener('change', function () {
                resetOptions(floorSelect, 'Select Floor');
                resetOptions(unitSelect, 'Select Unit');

                if (this.value) {
                    fetch(routes.getFloors.replace('__ID__', this.value))
                        .then(res => res.json())
                        .then(data => {
                            data.forEach(floor => {
                                const option = document.createElement('option');
                                option.value = floor.Id;
                                option.textContent = floor.FloorLabel;
                                floorSelect.appendChild(option);
                            });
                        })
                        .catch(() => alert('Failed to load floors.'));
                }
            });

            // Floor → Unit
            floorSelect.addEventListener('change', function () {
                resetOptions(unitSelect, 'Select Unit');

                if (this.value) {
                    fetch(routes.getUnits.replace('__ID__', this.value))
                        .then(res => res.json())
                        .then(data => {
                            data.forEach(unit => {
                                const option = document.createElement('option');
                                option.value = unit.Id;
                                option.textContent = unit.UnitCode;
                                unitSelect.appendChild(option);
                            });
                        })
                        .catch(() => alert('Failed to load units.'));
                }
            });

            // Calculate Total
            function calculateTotal() {
                let total = 0;
                chargeFields.forEach(input => {
                    total += parseFloat(input.value) || 0;
                });
                totalField.value = total;
            }

            chargeFields.forEach(input => {
                input.addEventListener('input', calculateTotal);
            });
        });
    </script>
@endsection
