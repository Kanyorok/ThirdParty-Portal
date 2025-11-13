@extends('layouts.app')
@section('title', 'Rent Invoice')
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
    <h4 class="fw-bold mb-3">Generate Rent Invoice</h4>

    <form action="{{ route('rentinvoice.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card shadow">
            <div class="card-header bg-light fw-bold">Lease Billing Details</div>
            <div class="card-body">

                <!-- Lease Selection -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Select Lease<span class="text-danger">*</span></label>
                        <select name="Lease" class="form-select" required>
                            <option value="">-- Select Lease --</option>
                            @foreach ($newleases as $newlease)
                                <option value="{{ $newlease->Id }}"
                                    data-rent="{{ $newlease->MonthlyRent ?? 0 }}"
                                    data-service="{{ $newlease->ServiceCharge ?? 0 }}"
                                    data-parking="{{ $newlease->ParkingFee ?? 0 }}"
                                    data-other="{{ $newlease->OtherCharges ?? 0 }}">
                                    LSno: {{ $newlease->LeaseNumber }} — Name: {{ $newlease->tenant->thirdParty->ThirdPartyName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Billing Month<span class="text-danger">*</span></label>
                        <input type="month" class="form-control" value="{{ old('BillingMonth', now()) }}" name="BillingMonth">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Invoice Date<span class="text-danger">*</span></label>
                        <input type="date" class="form-control" value="{{ old('InvoiceDate', now()) }}" name="InvoiceDate">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <label for="Description" class="form-label">Invoice Description</label>
                        <input 
                            type="text" class="form-control" id="Description" name="Description" 
                            placeholder="Enter a short description for this invoice"
                            value="{{ old('Description') }}">
                    </div>
                </div>


                <!-- Line Items Table -->
                <div class="table-responsive mb-3">
                    <table class="table table-bordered align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Line Item</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Currency</th>
                                <th>Tax</th>
                            </tr>
                        </thead>
<tbody>
    <tr>
        <td>Rent</td>
        <td><input type="text" class="form-control" name="DescriptionRent" value="Rent Payment"></td>
        <td><input type="number" class="form-control amount" name="RentAmount" step="0.01" min="0" value="{{ old('RentAmount', 0) }}"></td>
        <td>
            <select name="CurrencyRent" class="form-select" required>
                <option value="">-- Select Currency --</option>
                @foreach($currencies as $currency)
                    <option value="{{ $currency->Id }}">{{ $currency->Code }} — {{ $currency->Symbol }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <select name="TaxRent" class="form-select" required>
                <option value="">-- Select Tax Type --</option>
                @foreach($taxTypes as $tax)
                    <option value="{{ $tax->Id }}">{{ $tax->TaxTypeName }}</option>
                @endforeach
            </select>
        </td>
    </tr>

    <tr>
        <td>Service Charge</td>
        <td><input type="text" class="form-control" name="DescriptionService" value="Monthly Service Charge"></td>
        <td><input type="number" class="form-control amount" name="ServicesCharge" step="0.01" min="0" value="{{ old('ServicesCharge', 0) }}"></td>
        <td>
            <select name="CurrencyService" class="form-select" required>
                <option value="">-- Select Currency --</option>
                @foreach($currencies as $currency)
                    <option value="{{ $currency->Id }}">{{ $currency->Code }} — {{ $currency->Symbol }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <select name="TaxService" class="form-select" required>
                <option value="">-- Select Tax Type --</option>
                @foreach($taxTypes as $tax)
                    <option value="{{ $tax->Id }}">{{ $tax->TaxTypeName }}</option>
                @endforeach
            </select>
        </td>
    </tr>

    <tr>
        <td>Parking Fee</td>
        <td><input type="text" class="form-control" name="DescriptionParking" value="Parking Space"></td>
        <td><input type="number" class="form-control amount" name="ParkingFee" step="0.01" min="0" value="{{ old('ParkingFee', 0) }}"></td>
        <td>
            <select name="CurrencyParking" class="form-select" required>
                <option value="">-- Select Currency --</option>
                @foreach($currencies as $currency)
                    <option value="{{ $currency->Id }}">{{ $currency->Code }} — {{ $currency->Symbol }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <select name="TaxParking" class="form-select" required>
                <option value="">-- Select Tax Type --</option>
                @foreach($taxTypes as $tax)
                    <option value="{{ $tax->Id }}">{{ $tax->TaxTypeName }}</option>
                @endforeach
            </select>
        </td>
    </tr>

    <tr>
        <td>Other Charges</td>
        <td><input type="text" class="form-control" name="DescriptionOther" value="Miscellaneous"></td>
        <td><input type="number" class="form-control amount" name="OtherCharges" step="0.01" min="0" value="{{ old('OtherCharges', 0) }}"></td>
        <td>
            <select name="CurrencyOther" class="form-select" required>
                <option value="">-- Select Currency --</option>
                @foreach($currencies as $currency)
                    <option value="{{ $currency->Id }}">{{ $currency->Code }} — {{ $currency->Symbol }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <select name="TaxOther" class="form-select" required>
                <option value="">-- Select Tax Type --</option>
                @foreach($taxTypes as $tax)
                    <option value="{{ $tax->Id }}">{{ $tax->TaxTypeName }}</option>
                @endforeach
            </select>
        </td>
    </tr>
</tbody>

                    </table>
                </div>

                <!-- Total Amount -->
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Total Amount</label>
                        <input type="number" class="form-control bg-light fw-bold" name="TotalAmount" value="{{ old('TotalAmount', 0) }}" step="0.01" readonly>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-3">
                    <label class="form-label">Invoice Notes</label>
                    <textarea class="form-control" rows="2" name="InvoiceNotes" placeholder="Optional notes or remarks...">{{ old('InvoiceNotes') }}</textarea>
                </div>

                <a href="{{ route('rentinvoice.index') }}" class="btn btn-secondary">Back</a>
                <button type="submit" class="btn btn-success"
                    onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Generate Invoice
                </button>

            </div>
        </div>
    </form>
</div>

<!-- JavaScript -->
<script>
    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.amount').forEach(el => {
            total += parseFloat(el.value || 0);
        });
        document.querySelector('input[name="TotalAmount"]').value = total.toFixed(2);
    }

    // Auto-fill on lease selection
    document.querySelector('select[name="Lease"]').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        document.querySelector('input[name="RentAmount"]').value = parseFloat(selected.dataset.rent || 0).toFixed(2);
        document.querySelector('input[name="ServicesCharge"]').value = parseFloat(selected.dataset.service || 0).toFixed(2);
        document.querySelector('input[name="ParkingFee"]').value = parseFloat(selected.dataset.parking || 0).toFixed(2);
        document.querySelector('input[name="OtherCharges"]').value = parseFloat(selected.dataset.other || 0).toFixed(2);
        calculateTotal();
    });

    // Recalculate when any amount changes
    document.querySelectorAll('.amount').forEach(el => el.addEventListener('input', calculateTotal));

    // Format on submit
    document.querySelector('form').addEventListener('submit', function () {
        document.querySelectorAll('.amount').forEach(el => {
            el.value = parseFloat(el.value || 0).toFixed(2);
        });
    });

    // Initialize
    calculateTotal();
</script>
@endsection
