@extends('layouts.app')

@section('title', 'Edit Property Rate & Pricing')

@section('content')
<div class="container mt-4" style="max-width: 1100px;">

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

    <form action="{{ route('propertyrateandpricing.update', $pricing->Id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- PROPERTY / BLOCK / FLOOR / UNIT --}}
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Property</label>
                <select class="form-control" id="PropertyId" name="PropertyId" required>
                    <option value="">-- Select Property --</option>
                    @foreach ($property as $p)
                        <option value="{{ $p->Id }}" {{ $pricing->PropertyId == $p->Id ? 'selected' : '' }}>
                            {{ $p->PropertyName }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Block</label>
                <select class="form-control" id="BlockId" name="BlockId" required>
                    <option value="">-- Select Block --</option>
                </select>
            </div>

            <div class="col-md-3">
                <label>Floor</label>
                <select class="form-control" id="FloorId" name="FloorId" required>
                    <option value="">-- Select Floor --</option>
                </select>
            </div>

            <div class="col-md-3">
                <label>Unit</label>
                <select class="form-control" id="UnitId" name="UnitId" required>
                    <option value="">-- Select Unit --</option>
                </select>
            </div>
        </div>

        {{-- CURRENCY / TAX --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Currency</label>
                <select class="form-control" name="CurrencyId" required>
                    @foreach ($currencies as $cur)
                        <option value="{{ $cur->Id }}" {{ $pricing->CurrencyId == $cur->Id ? 'selected' : '' }}>
                            {{ $cur->Code }} - {{ $cur->Symbol }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label>Tax Rule</label>
                <select class="form-control" name="TaxId" required>
                    @foreach ($Taxes as $tax)
                        <option value="{{ $tax->Id }}" {{ $pricing->TaxId == $tax->Id ? 'selected' : '' }}>
                            {{ $tax->taxType->TaxTypeName }} - {{ $tax->Rate }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- PRICING FIELDS --}}
        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Rent</label>
                <input type="number" name="Rent" class="form-control" value="{{ $pricing->Rent }}" required>
            </div>

            <div class="col-md-4 mb-3">
                <label>Service Charge</label>
                <input type="number" name="ServiceCharge" class="form-control" value="{{ $pricing->ServiceCharge }}">
            </div>

            <div class="col-md-4 mb-3">
                <label>Parking Fee</label>
                <input type="number" name="ParkingFee" class="form-control" value="{{ $pricing->ParkingFee }}">
            </div>

            <div class="col-md-4 mb-3">
                <label>Other Charges</label>
                <input type="number" name="OtherCharges" class="form-control" value="{{ $pricing->OtherCharges }}">
            </div>

            <div class="col-md-4 mb-3">
                <label>Deposit Amount</label>
                <input type="number" name="DepositAmount" class="form-control" value="{{ $pricing->DepositAmount }}">
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
            <a href="{{ route('propertyrateandpricing.index') }}" class="btn btn-outline-secondary">Back</a>
            <button type="submit" class="btn btn-primary">Update</button>
        </div>
    </form>
</div>

{{-- AJAX SCRIPT --}}
<script>
document.addEventListener('DOMContentLoaded', function() {

    const selectedBlock = "{{ $pricing->BlockId }}";
    const selectedFloor = "{{ $pricing->FloorId }}";
    const selectedUnit  = "{{ $pricing->UnitId }}";

    loadBlocks();

    document.getElementById('PropertyId').addEventListener('change', loadBlocks);
    document.getElementById('BlockId').addEventListener('change', loadFloors);
    document.getElementById('FloorId').addEventListener('change', loadUnits);

    function loadBlocks() {
        const propertyId = document.getElementById('PropertyId').value;
        const blockSelect = document.getElementById('BlockId');
        blockSelect.innerHTML = '<option value="">-- Select Block --</option>';
        if (!propertyId) return;

        fetch(`{{ route('getblockbyproperty.rate', ['PropertyId' => '__id__']) }}`
            .replace('__id__', propertyId))
            .then(res => res.json())
            .then(blocks => {
                blocks.forEach(b => {
                    blockSelect.innerHTML += `<option value="${b.Id}" ${b.Id == selectedBlock ? 'selected' : ''}>${b.BlockName}</option>`;
                });
                loadFloors();
            });
    }

    function loadFloors() {
        const blockId = document.getElementById('BlockId').value;
        const floorSelect = document.getElementById('FloorId');
        floorSelect.innerHTML = '<option value="">-- Select Floor --</option>';
        if (!blockId) return;

        fetch(`{{ route('getfloorbyblock.rate', ['BlockId' => '__id__']) }}`
            .replace('__id__', blockId))
            .then(res => res.json())
            .then(floors => {
                floors.forEach(f => {
                    floorSelect.innerHTML += `<option value="${f.Id}" ${f.Id == selectedFloor ? 'selected' : ''}>${f.FloorLabel}</option>`;
                });
                loadUnits();
            });
    }

    function loadUnits() {
        const floorId = document.getElementById('FloorId').value;
        const unitSelect = document.getElementById('UnitId');
        unitSelect.innerHTML = '<option value="">-- Select Unit --</option>';
        if (!floorId) return;

        fetch(`{{ route('getunitsbyfloor.rate', ['FloorId' => '__id__']) }}`
            .replace('__id__', floorId))
            .then(res => res.json())
            .then(units => {
                units.forEach(u => {
                    unitSelect.innerHTML += `<option value="${u.Id}" ${u.Id == selectedUnit ? 'selected' : ''}>${u.UnitCode}</option>`;
                });
            });
    }

});
</script>

@endsection
