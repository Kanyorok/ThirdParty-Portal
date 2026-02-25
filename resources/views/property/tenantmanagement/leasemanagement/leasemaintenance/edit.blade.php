@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'Edit Lease Agreement')

@section('content')
<div class="container mt-4">

<form method="POST" action="{{ route('addlease.update', $newlease->Id) }}" enctype="multipart/form-data" id="leaseForm">
    @csrf
    @method('PUT')

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary fw-bold py-3">Lease Agreement</div>

        <div class="card-body">

            {{-- ================= TENANT + PROPERTY ================ --}}
            <h5 class="fw-bold border-bottom pb-2 mb-3">Tenant & Property Information</h5>

            <div class="row g-3 mb-4">

                {{-- Lease Number --}}
                <div class="col-md-4">
                    <label class="form-label">Lease Number</label>
                    <input type="text" class="form-control" value="{{ $newlease->LeaseNumber }}" disabled>
                </div>

                {{-- Tenant --}}
                <div class="col-md-4">
                    <label class="form-label">Tenant</label>
                    <input type="text" class="form-control" 
                        value="{{ $newlease->tenant->thirdParty->ThirdPartyName ?? '' }}" disabled>
                    <input type="hidden" name="Tenant" value="{{ $newlease->Tenant }}">
                </div>

                {{-- Property --}}
                <div class="col-md-4">
                    <label class="form-label">Property <span class="text-danger">*</span></label>
                    <select name="PropertyID" id="property-select" class="form-select shadow-sm @error('PropertyID') is-invalid @enderror" required>
                        <option value="">-- Select Property --</option>
                        @foreach ($properties as $property)
                            <option value="{{ $property->Id }}"
                                {{ $property->Id == $newlease->PropertyID ? 'selected' : '' }}>
                                {{ $property->PropertyName }}
                            </option>
                        @endforeach
                    </select>
                    @error('PropertyID')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- ================= BLOCK / FLOOR / UNIT ================ --}}
            <h5 class="fw-bold border-bottom pb-2 mb-3">Block / Floor / Unit</h5>

            <div class="row g-3 mb-4">
                
                {{-- Block --}}
                <div class="col-md-4">
                    <label class="form-label">Block <span class="text-danger">*</span></label>
                    <select name="BlockID" id="block-select" class="form-select shadow-sm @error('BlockID') is-invalid @enderror" required>
                        <option value="{{ $newlease->BlockID }}" selected>
                            {{ $newlease->block->BlockName ?? 'Current Block' }}
                        </option>
                    </select>
                    @error('BlockID')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Floor --}}
                <div class="col-md-4">
                    <label class="form-label">Floor <span class="text-danger">*</span></label>
                    <select name="FloorID" id="floor-select" class="form-select shadow-sm @error('FloorID') is-invalid @enderror" required>
                        <option value="{{ $newlease->FloorID }}" selected>
                            {{ $newlease->floor->FloorLabel ?? 'Current Floor' }}
                        </option>
                    </select>
                    @error('FloorID')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Unit --}}
                <div class="col-md-4">
                    <label class="form-label">Unit <span class="text-danger">*</span></label>
                    <select name="Unit" id="unit-select" class="form-select shadow-sm @error('Unit') is-invalid @enderror" required>
                        <option value="{{ $newlease->Unit }}" selected>
                            {{ $newlease->unit->UnitCode ?? 'Current Unit' }}
                        </option>
                    </select>
                    @error('Unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- ================= LEASE DATES ================ --}}
            <h5 class="fw-bold border-bottom pb-2 mb-3">Lease Duration</h5>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Start Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control shadow-sm @error('StartDate') is-invalid @enderror" name="StartDate"
                        value="{{ Carbon::parse($newlease->StartDate)->format('Y-m-d') }}">
                    @error('StartDate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">End Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control shadow-sm @error('EndDate') is-invalid @enderror" name="EndDate"
                        value="{{ Carbon::parse($newlease->EndDate)->format('Y-m-d') }}">
                    @error('EndDate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- ================= PAYMENT FREQUENCY ================ --}}
            <div class="mb-4">
                <label class="form-label">Payment Frequency <span class="text-danger">*</span></label>
                <select name="PaymentFrequency" class="form-select shadow-sm @error('PaymentFrequency') is-invalid @enderror" required>
                    @foreach ($codes as $code)
                        <option value="{{ $code->ID }}"
                            {{ $code->ID == $newlease->PaymentFrequency ? 'selected' : '' }}>
                            {{ $code->Description }}
                        </option>
                    @endforeach
                </select>
                @error('PaymentFrequency')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- ================= CURRENCY & TAX ================ --}}
            <h5 class="fw-bold border-bottom pb-2 mb-3">Financial Details</h5>

            <div class="row g-3 mb-4">

                {{-- Currency --}}
                <div class="col-md-6">
                    <label class="form-label">Currency <span class="text-danger">*</span></label>
                    <select name="CurrencyId" class="form-select shadow-sm @error('CurrencyId') is-invalid @enderror" required>
                        <option value="">-- Select Currency --</option>
                        @foreach ($Currencies as $currency)
                            <option value="{{ $currency->Id }}"
                                {{ $currency->Id == $newlease->CurrencyId ? 'selected' : '' }}>
                                {{ $currency->Code }} - {{ $currency->Symbol }}
                            </option>
                        @endforeach
                    </select>
                    @error('CurrencyId')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Tax --}}
                <div class="col-md-6">
                    <label class="form-label">Tax Rule <span class="text-danger">*</span></label>
                    <select name="TaxId" class="form-select shadow-sm @error('TaxId') is-invalid @enderror" required>
                        <option value="">-- Select Tax Rule --</option>
                        @foreach ($taxtypes as $tax)
                            <option value="{{ $tax->Id }}"
                                {{ $tax->Id == $newlease->TaxId ? 'selected' : '' }}>
                                {{ $tax->taxType->TaxTypeName }} ({{ $tax->Rate }}%)
                            </option>
                        @endforeach
                    </select>
                    @error('TaxId')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- ================= CHARGES ================ --}}
            <div class="row g-3 mb-4">

                <div class="col-md-4">
                    <label class="form-label">Rent <span class="text-danger">*</span></label>
                    <input type="text" class="form-control shadow-sm charge-field @error('MonthlyRent') is-invalid @enderror"
                        name="MonthlyRent" value="{{ $newlease->MonthlyRent }}" required>
                    @error('MonthlyRent')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Deposit <span class="text-danger">*</span></label>
                    <input type="text" class="form-control shadow-sm charge-field @error('Deposit') is-invalid @enderror"
                        name="Deposit" value="{{ $newlease->Deposit }}" required>
                    @error('Deposit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Service Charge <span class="text-danger">*</span></label>
                    <input type="text" class="form-control shadow-sm charge-field @error('ServiceCharge') is-invalid @enderror"
                        name="ServiceCharge" value="{{ $newlease->ServiceCharge }}" required>
                    @error('ServiceCharge')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Parking Fee <span class="text-danger">*</span></label>
                    <input type="text" class="form-control shadow-sm charge-field @error('ParkingFee') is-invalid @enderror"
                        name="ParkingFee" value="{{ $newlease->ParkingFee }}" required>
                    @error('ParkingFee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Other Charges <span class="text-danger">*</span></label>
                    <input type="text" class="form-control shadow-sm charge-field @error('OtherCharges') is-invalid @enderror"
                        name="OtherCharges" value="{{ $newlease->OtherCharges }}" required>
                    @error('OtherCharges')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Total Payable (Excl. Deposit) <span class="text-danger">*</span></label>
                    <input type="text" id="TotalPayable" class="form-control shadow-sm" readonly>
                </div>
            </div>

            {{-- DUE DATE --}}
            <div class="mb-4">
                <label class="form-label">Payment Due Date (1–28) <span class="text-danger">*</span></label>
                <input type="number" name="DueDay" class="form-control shadow-sm @error('DueDay') is-invalid @enderror"
                    min="1" max="28" value="{{ $newlease->DueDay }}" required>
                @error('DueDay')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- SPECIAL TERMS --}}
            <div class="mb-4">
                <label class="form-label">Special Terms</label>
                <textarea class="form-control shadow-sm @error('SpecialTerms') is-invalid @enderror" rows="3"
                    name="SpecialTerms">{{ $newlease->SpecialTerms }}</textarea>
                @error('SpecialTerms')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- DOCUMENTS --}}
            <div class="mb-4">
                <label class="form-label">Existing Documents</label>
                <div class="p-3 bg-light border rounded">
                    @forelse($newlease->documents()->get() as $document)
                        {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
                    @empty
                        <span>No documents available.</span>
                    @endforelse
                </div>

                <label class="form-label mt-2">Upload New Documents</label>
                <input type="file" name="Document[]" class="form-control shadow-sm @error('Document') is-invalid @enderror"
                    accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx" multiple>
                @error('Document')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- BUTTONS --}}
            <div class="d-flex gap-3 justify-content-between">
                <a href="{{ route('addlease.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>

                <button type="submit" class="btn btn-success px-4"
                    onclick="this.disabled=true;this.innerText='Updating...';this.form.submit();">
                    Update Lease
                </button>
            </div>

        </div>
    </div>

</form>
</div>

{{-- ================= JS ================= --}}
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

    const chargeFields = document.querySelectorAll('.charge-field');
    const totalField = document.getElementById('TotalPayable');

    const rentInput = document.querySelector('input[name="MonthlyRent"]');
    const depositInput = document.querySelector('input[name="Deposit"]');
    const serviceInput = document.querySelector('input[name="ServiceCharge"]');
    const parkingInput = document.querySelector('input[name="ParkingFee"]');
    const otherInput = document.querySelector('input[name="OtherCharges"]');

    const taxInput = document.querySelector('select[name="TaxId"]');
    const currencyInput = document.querySelector('select[name="CurrencyId"]');

    // ================= NUMBER FORMATTING WITH COMMAS =================
    const formatNumber = (value) => value ? parseFloat(value).toLocaleString('en-US') : '';
    const unformatNumber = (value) => parseFloat(value.replace(/,/g, '')) || 0;

    chargeFields.forEach(input => {
        input.type = "text"; // so commas display
        input.value = formatNumber(input.value);

        input.addEventListener('input', function () {
            let raw = this.value.replace(/,/g,'');
            if(!isNaN(raw) && raw !== '') this.value = formatNumber(raw);
            calculateTotal();
        });
    });

    // ================= TOTAL CALCULATION EXCLUDING DEPOSIT =================
    const calculateTotal = () => {
        let total = 0;
        chargeFields.forEach(i => {
            if(i.name !== 'Deposit') {
                total += unformatNumber(i.value);
            }
        });
        totalField.type = "text";
        totalField.value = formatNumber(total);
    };
    calculateTotal();

    // Remove commas before submitting form
    document.getElementById('leaseForm').addEventListener('submit', function() {
        chargeFields.forEach(input => {
            input.value = unformatNumber(input.value);
        });
    });

    // ================= RESET OPTIONS =================
    const resetOptions = (select, lbl) => {
        select.innerHTML = `<option value="">-- ${lbl} --</option>`;
    };

    // PROPERTY → BLOCKS
    propertySelect.addEventListener('change', function () {
        resetOptions(blockSelect, 'Select Block');
        resetOptions(floorSelect, 'Select Floor');
        resetOptions(unitSelect, 'Select Unit');

        if (this.value) {
            fetch(routes.getBlocks.replace('__ID__', this.value))
                .then(r => r.json())
                .then(blocks => {
                    blocks.forEach(b => {
                        blockSelect.insertAdjacentHTML('beforeend',
                            `<option value="${b.Id}">${b.BlockName}</option>`);
                    });
                });
        }
    });

    // BLOCK → FLOORS
    blockSelect.addEventListener('change', function () {
        resetOptions(floorSelect, 'Select Floor');
        resetOptions(unitSelect, 'Select Unit');

        if (this.value) {
            fetch(routes.getFloors.replace('__ID__', this.value))
                .then(r => r.json())
                .then(floors => {
                    floors.forEach(f =>
                        floorSelect.insertAdjacentHTML('beforeend',
                            `<option value="${f.Id}">${f.FloorLabel}</option>`));
                });
        }
    });

    // FLOOR → UNITS
    floorSelect.addEventListener('change', function () {
        resetOptions(unitSelect, 'Select Unit');

        if (this.value) {
            fetch(routes.getUnits.replace('__ID__', this.value))
                .then(r => r.json())
                .then(units => {
                    units.forEach(u =>
                        unitSelect.insertAdjacentHTML('beforeend',
                            `<option value="${u.Id}">${u.UnitCode}</option>`));
                });
        }
    });

    // UNIT → LOAD PRICING LIKE CREATE
    unitSelect.addEventListener('change', function () {
        if (!this.value) return;

        fetch(routes.getPricing.replace('__ID__', this.value))
            .then(r => r.json())
            .then(p => {
                if (!p) return;

                rentInput.value = formatNumber(p.Rent ?? '');
                depositInput.value = formatNumber(p.DepositAmount ?? '');
                serviceInput.value = formatNumber(p.ServiceCharge ?? '');
                parkingInput.value = formatNumber(p.ParkingFee ?? '');
                otherInput.value = formatNumber(p.OtherCharges ?? '');

                taxInput.value = p.TaxId ?? '';
                currencyInput.value = p.CurrencyId ?? '';

                calculateTotal();
            });
    });

});
</script>

@endsection

@section('scripts')
    @include('snippets.actions.preview-files')
@endsection