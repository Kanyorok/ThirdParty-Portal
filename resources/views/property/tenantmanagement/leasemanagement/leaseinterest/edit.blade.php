@extends('layouts.app')

@section('title', 'Edit Property Interest')

@section('content')
<div class="container mt-5" style="max-width:1100px">

    <div class="card shadow border-0">
        <div class="card-body">

            {{-- Errors --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Please fix the following:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('property-interest.update', $interest->Id) }}">
                @csrf
                @method('PUT')

                {{-- ================= TENANT ================= --}}
                <h6 class="fw-bold mb-2">Tenant Info</h6>
                <hr>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Tenant Name<span class="text-danger">*</span></label>
                        <input type="text" name="TenantName" class="form-control" value="{{ old('TenantName', $interest->tenant->thirdParty->ThirdPartyName) }}" readonly>
                        <input type="text" name="TenantId" class="form-control" value="{{ old('TenantId', $interest->TenantId) }}" hidden>
                    </div>
                </div>

                {{-- ================= PROPERTY FLOW ================= --}}
                <h6 class="fw-bold mb-2">Property Selection</h6>
                <hr>
                <div class="row g-3 mb-4">

                    <div class="col-md-3">
                        <label class="form-label">Property <span class="text-danger">*</span></label>
                        <select id="property_id" name="PropertyId" class="form-select" required>
                            <option value="">Select Property</option>
                            @foreach($properties as $property)
                                <option value="{{ $property->Id }}"
                                    {{ old('PropertyId', $interest->PropertyId) == $property->Id ? 'selected' : '' }}>
                                    {{ $property->PropertyName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Block <span class="text-danger">*</span></label>
                        <select id="block_id" name="BlockId" class="form-select" required>
                            <option value="">Select Block</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Floor <span class="text-danger">*</span></label>
                        <select id="floor_id" name="FloorId" class="form-select" required>
                            <option value="">Select Floor</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Unit <span class="text-danger">*</span></label>
                        <select id="unit_id" name="UnitId" class="form-select" required>
                            <option value="">Select Unit</option>
                        </select>
                    </div>

                </div>

                {{-- ================= PRICING ================= --}}
                <div class="mb-4" id="unit-pricing" style="display:none;">
                    <div class="card border-primary shadow-sm">
                        <div class="card-header bg-primary text-white fw-semibold">
                            Unit Pricing Breakdown
                        </div>
                        <div class="card-body p-2">
                            <table class="table table-bordered table-sm mb-0">
                                <tbody>
                                    <tr><th>Rent</th><td id="pricing-rent"></td></tr>
                                    <tr><th>Parking Fee</th><td id="pricing-parking"></td></tr>
                                    <tr><th>Service Charge</th><td id="pricing-service"></td></tr>
                                    <tr><th>Other Charges</th><td id="pricing-other"></td></tr>
                                    <tr><th>Deposit</th><td id="pricing-deposit"></td></tr>
                                    <tr class="table-success fw-bold">
                                        <th>Total (Excl. Deposit)</th>
                                        <td id="pricing-total"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- ================= DATES ================= --}}
                <h6 class="fw-bold mb-2">Interest Period</h6>
                <hr>
                <div class="row g-3 mb-4">

                    <div class="col-md-4">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date"
                               name="InterestedStartDate"
                               class="form-control"
                               value="{{ old('InterestedStartDate', \Carbon\Carbon::parse($interest->InterestedStartDate)->format('Y-m-d')) }}"
                               required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="date"
                               name="InterestedEndDate"
                               class="form-control"
                               value="{{ old('InterestedEndDate', \Carbon\Carbon::parse($interest->InterestedEndDate)->format('Y-m-d')) }}"
                               required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Payment Frequency <span class="text-danger">*</span></label>
                        <select name="PaymentFrequency" class="form-select" required>
                            <option value="">Select Frequency</option>
                            @foreach($frequencies as $freq)
                                <option value="{{ $freq->ID }}"
                                    {{ old('PaymentFrequency', $interest->PaymentFrequency) == $freq->ID ? 'selected' : '' }}>
                                    {{ $freq->Description }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                {{-- ================= ADDITIONAL INFO ================= --}}
                <h6 class="fw-bold mb-2">Additional Information <span class="text-danger">*</span></h6>
                <div class="mb-4">
                    <textarea name="AdditionalInformation"
                              class="form-control"
                              rows="4"
                              required>{{ old('AdditionalInformation', $interest->AdditionalInformation) }}</textarea>
                </div>

                {{-- ================= FOOTER ================= --}}
                <div class="d-flex justify-content-between align-items-center border-top pt-3">
                    <a href="{{ route('property-interest.index') }}" class="btn btn-outline-secondary px-4">
                        ← Back
                    </a>

                    <button class="btn btn-primary fw-semibold px-4">
                        Update Interest
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- ================= JAVASCRIPT ================= --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(function() {

    let blockUrl   = "{{ route('getblockbyproperty.interest', ['PropertyId' => '__ID__']) }}";
    let floorUrl   = "{{ route('getfloorbyblock.interest', ['BlockId' => '__ID__']) }}";
    let unitUrl    = "{{ route('getunitbyfloor.interest', ['FloorId' => '__ID__']) }}";
    let pricingUrl = "{{ route('getunitpricing.interest', ['UnitId' => '__ID__']) }}";

    let selectedProperty = "{{ old('PropertyId', $interest->PropertyId) }}";
    let selectedBlock    = "{{ old('BlockId', $interest->BlockId) }}";
    let selectedFloor    = "{{ old('FloorId', $interest->FloorId) }}";
    let selectedUnit     = "{{ old('UnitId', $interest->UnitId) }}";

    function formatAmount(val) {
        return val != null ? Number(val).toLocaleString() : 'N/A';
    }

    function updateTotal(rent, parking, service, other) {
        return formatAmount(
            (Number(rent)||0) +
            (Number(parking)||0) +
            (Number(service)||0) +
            (Number(other)||0)
        );
    }

    // PROPERTY → BLOCK
    $('#property_id').change(function () {
        let id = $(this).val();
        $('#block_id').html('<option>Loading...</option>');
        $('#floor_id').html('<option value="">Select Floor</option>');
        $('#unit_id').html('<option value="">Select Unit</option>');
        $('#unit-pricing').hide();

        if(id) {
            $.get(blockUrl.replace('__ID__', id), function(res){
                let html = '<option value="">Select Block</option>';
                res.forEach(b => {
                    html += `<option value="${b.Id}" ${selectedBlock == b.Id ? 'selected' : ''}>${b.BlockName}</option>`;
                });
                $('#block_id').html(html).trigger('change');
            });
        }
    });

    // BLOCK → FLOOR
    $('#block_id').change(function () {
        let id = $(this).val();
        $('#floor_id').html('<option>Loading...</option>');
        $('#unit-pricing').hide();

        if(id) {
            $.get(floorUrl.replace('__ID__', id), function(res){
                let html = '<option value="">Select Floor</option>';
                res.forEach(f => {
                    html += `<option value="${f.Id}" ${selectedFloor == f.Id ? 'selected' : ''}>${f.FloorLabel}</option>`;
                });
                $('#floor_id').html(html).trigger('change');
            });
        }
    });

    // FLOOR → UNIT
    $('#floor_id').change(function () {
        let id = $(this).val();
        $('#unit_id').html('<option>Loading...</option>');
        $('#unit-pricing').hide();

        if(id) {
            $.get(unitUrl.replace('__ID__', id), function(res){
                let html = '<option value="">Select Unit</option>';
                res.forEach(u => {
                    html += `<option value="${u.Id}" ${selectedUnit == u.Id ? 'selected' : ''}>${u.UnitCode}</option>`;
                });
                $('#unit_id').html(html).trigger('change');
            });
        }
    });

    // UNIT → PRICING
    $('#unit_id').change(function () {
        let id = $(this).val();
        if(id) {
            $.get(pricingUrl.replace('__ID__', id), function(res){
                if(res){
                    $('#pricing-rent').text(formatAmount(res.Rent));
                    $('#pricing-parking').text(formatAmount(res.ParkingFee));
                    $('#pricing-service').text(formatAmount(res.ServiceCharge));
                    $('#pricing-other').text(formatAmount(res.OtherCharges));
                    $('#pricing-deposit').text(formatAmount(res.DepositAmount));
                    $('#pricing-total').text(updateTotal(res.Rent, res.ParkingFee, res.ServiceCharge, res.OtherCharges));
                    $('#unit-pricing').show();
                }
            });
        }
    });

    // AUTO LOAD EXISTING DATA
    if(selectedProperty){
        $('#property_id').val(selectedProperty).trigger('change');
    }

});
</script>
@endsection
