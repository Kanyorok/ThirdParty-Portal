@extends('layouts.app')
@section('title', 'Property Rate & Pricing Setup')

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

<div class="container mt-4">

    <form action="{{ route(name: 'propertyrateandpricing.store') }}" method="POST">
        @csrf

        <div class="card shadow">

            <div class="card-body">

                {{-- Row 1: Property / Block / Floor / Unit --}}
                <div class="row g-2 mb-3">

                    <div class="col-md-3">
                        <label class="form-label">Property<span class="text-danger">*</span></label>
                        <select name="PropertyId" id="property-select" class="form-select" required>
                            <option value="">-- Select Property --</option>
                            @foreach ($property as $item)
                                <option value="{{ $item->Id }}">{{ $item->PropertyName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Block<span class="text-danger">*</span></label>
                        <select name="BlockId" id="block-select" class="form-select" required>
                            <option value="">-- Select Block --</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Floor<span class="text-danger">*</span></label>
                        <select name="FloorId" id="floor-select" class="form-select" required>
                            <option value="">-- Select Floor --</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Unit<span class="text-danger">*</span></label>
                        <select name="UnitId" id="unit-select" class="form-select" required>
                            <option value="">-- Select Unit --</option>
                        </select>
                    </div>

                </div>

                {{-- Row 2: Rent / Parking Fee --}}
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Rent Amount<span class="text-danger">*</span></label>
                        <input type="number" name="Rent" class="form-control" placeholder="e.g. 50000" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Parking Fee<span class="text-danger">*</span></label>
                        <input type="number" name="ParkingFee" class="form-control" placeholder="e.g. 3000" required>
                    </div>
                </div>

                {{-- Row 3: Service Charge / Other Charges --}}
                <div class="row g-2 mb-3">

                    <div class="col-md-6">
                        <label class="form-label">Service Charge<span class="text-danger">*</span></label>
                        <input type="number" name="ServiceCharge" class="form-control" placeholder="e.g. 2000" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Other Charges<span class="text-danger">*</span></label>
                        <input type="number" name="OtherCharges" class="form-control" placeholder="e.g. 1000" required>
                    </div>

                </div>

                {{-- Row 4: Deposit / Currency / Tax --}}
                <div class="row g-2 mb-3">

                    <div class="col-md-4">
                        <label class="form-label">Deposit Amount<span class="text-danger">*</span></label>
                        <input type="number" name="DepositAmount" class="form-control" placeholder="e.g. 60000" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Currency<span class="text-danger">*</span></label>
                        <select class="form-select" name="CurrencyId" required>
                            <option value="">-- Select Currency --</option>
                            @foreach($currencies as $cur)
                                <option value="{{ $cur->Id }}">{{ $cur->Code }} - {{ $cur->Symbol }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tax Rule<span class="text-danger">*</span></label>
                        <select class="form-select" name="TaxId" required>
                            <option value="">-- Select Tax --</option>
                            @foreach($Taxes as $tax)
                                <option value="{{ $tax->Id }}">{{ $tax->taxType->TaxTypeName }} - {{ $tax->Rate }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="btn btn-success"
                        onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
                    Save Pricing
                </button>

            </div>
        </div>

    </form>

</div>

{{-- DYNAMIC LOADING SCRIPTS --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const propertySelect = document.getElementById('property-select');
    const blockSelect = document.getElementById('block-select');
    const floorSelect = document.getElementById('floor-select');
    const unitSelect = document.getElementById('unit-select');

    // Reset dropdowns
    function reset(select, placeholder) {
        select.innerHTML = `<option value="">-- ${placeholder} --</option>`;
    }

    // Load Blocks
    propertySelect.addEventListener('change', function () {
        reset(blockSelect, "Select Block");
        reset(floorSelect, "Select Floor");
        reset(unitSelect, "Select Unit");

        if (this.value) {
            const url = `{{ route('getblockbyproperty.rate', ':id') }}`.replace(':id', this.value);

            fetch(url)
                .then(res => res.json())
                .then(blocks => {
                    blocks.forEach(b => {
                        blockSelect.innerHTML += `<option value="${b.Id}">${b.BlockName}</option>`;
                    });
                });
        }
    });

    // Load Floors
    blockSelect.addEventListener('change', function () {
        reset(floorSelect, "Select Floor");
        reset(unitSelect, "Select Unit");

        if (this.value) {
            const url = `{{ route('getfloorbyblock.rate', ':id') }}`.replace(':id', this.value);

            fetch(url)
                .then(res => res.json())
                .then(floors => {
                    floors.forEach(f => {
                        floorSelect.innerHTML += `<option value="${f.Id}">${f.FloorLabel}</option>`;
                    });
                });
        }
    });

    // Load Units
    floorSelect.addEventListener('change', function () {
        reset(unitSelect, "Select Unit");

        if (this.value) {
            const url = `{{ route('getunitsbyfloor.rate', ':id') }}`.replace(':id', this.value);

            fetch(url)
                .then(res => res.json())
                .then(units => {
                    units.forEach(u => {
                        unitSelect.innerHTML += `<option value="${u.Id}">${u.UnitCode}</option>`;
                    });
                });
        }
    });

});
</script>

@endsection
