@php
    use Carbon\Carbon;

    $currency = $leaseschedule->lease->currency->SymbolNative ?? 'KES';

    $baseRent = $leaseschedule->BaseRent ?? 0;
    $serviceCharge = $leaseschedule->ServiceCharge ?? 0;
    $parkingFee = $leaseschedule->ParkingFee ?? 0;
    $otherCharges = $leaseschedule->OtherCharges ?? 0;

    // Subtotal before tax
    $subTotal = $baseRent + $serviceCharge + $parkingFee + $otherCharges;

    // VAT 16% (tax applies to baseRent + serviceCharge)
    $taxRate = (($leaseschedule->lease->taxRule->Rate)/100) ?? '1';
    $taxableAmount = $baseRent + $serviceCharge;
    $taxAmount = $taxableAmount * $taxRate;

    // Grand total
    $grandTotal = $subTotal + $taxAmount;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lease Schedule Print</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f9f9f9;
            margin: 40px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h2 {
            font-size: 26px;
            margin: 0;
        }
        .header p {
            font-size: 14px;
            color: #777;
        }
        .form-row {
            display: flex;
            margin-bottom: 18px;
        }
        .form-label {
            width: 40%;
            font-weight: 600;
        }
        .form-value {
            width: 60%;
            border-bottom: 1px solid #999;
            padding-bottom: 4px;
        }
        .total-row {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 2px solid #000;
            font-size: 18px;
            font-weight: 700;
        }
        .tax-row {
            font-weight: 600;
        }
        .grand-total-row {
            font-weight: 700;
        }
        @media print {
            body { margin: 0; background: #fff; }
            .container { box-shadow: none; padding: 20px; }
        }
    </style>
</head>

<body onload="window.print()">

<div class="container">

    <div class="header">
        <h2>{{ config('app.name') }}</h2>
        <h2>Lease Schedule Summary</h2>
        <p>Generated on {{ now()->format('d M Y') }}</p>
    </div>

    <div class="form-row">
        <div class="form-label">Lease Number</div>
        <div class="form-value">{{ $leaseschedule->lease->LeaseNumber }}</div>
    </div>

    <div class="form-row">
        <div class="form-label">Tenant Name</div>
        <div class="form-value">{{ $leaseschedule->lease->tenant->thirdParty->ThirdPartyName }}</div>
    </div>

    <div class="form-row">
        <div class="form-label">Property</div>
        <div class="form-value">{{ $leaseschedule->lease->property->PropertyName }}</div>
    </div>

    <div class="form-row">
        <div class="form-label">Payment Frequency</div>
        <div class="form-value">{{ $leaseschedule->paymentFrequency->Description }}</div>
    </div>

    <div class="form-row">
        <div class="form-label">Start Date</div>
        <div class="form-value">{{ Carbon::parse($leaseschedule->StartDate)->format('d M Y') }}</div>
    </div>

    <div class="form-row">
        <div class="form-label">End Date</div>
        <div class="form-value">{{ Carbon::parse($leaseschedule->EndDate)->format('d M Y') }}</div>
    </div>

    <!-- Charges -->
    <div class="form-row">
        <div class="form-label">Base Rent</div>
        <div class="form-value">{{ $currency }} {{ number_format($baseRent, 2) }}</div>
    </div>

    <div class="form-row">
        <div class="form-label">Service Charge</div>
        <div class="form-value">{{ $currency }} {{ number_format($serviceCharge, 2) }}</div>
    </div>

    <div class="form-row">
        <div class="form-label">Parking Fee</div>
        <div class="form-value">{{ $currency }} {{ number_format($parkingFee, 2) }}</div>
    </div>

    <div class="form-row">
        <div class="form-label">Other Charges</div>
        <div class="form-value">{{ $currency }} {{ number_format($otherCharges, 2) }}</div>
    </div>

    <!-- Subtotal -->
    <div class="form-row total-row">
        <div class="form-label">Sub Total</div>
        <div class="form-value">{{ $currency }} {{ number_format($subTotal, 2) }}</div>
    </div>

    <!-- Tax -->
    <div class="form-row tax-row">
        <div class="form-label">VAT ({{ $leaseschedule->lease->taxRule->Rate }}%)</div>
        <div class="form-value">{{ $currency }} {{ number_format($taxAmount, 2) }}</div>
    </div>

    <!-- Grand Total -->
    <div class="form-row grand-total-row">
        <div class="form-label">Grand Total</div>
        <div class="form-value">{{ $currency }} {{ number_format($grandTotal, 2) }}</div>
    </div>

</div>

</body>
</html>
