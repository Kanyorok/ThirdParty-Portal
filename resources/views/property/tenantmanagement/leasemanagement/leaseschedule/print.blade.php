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
            margin-bottom: 20px;
        }

        .form-label {
            width: 40%;
            font-weight: 600;
            padding-right: 20px;
        }

        .form-value {
            width: 60%;
            border-bottom: 1px solid #999;
            padding-bottom: 5px;
            font-size: 15px;
        }

        .btn-print {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            margin-top: 20px;
            cursor: pointer;
        }

        @media print {
            .btn-print, .no-print {
                display: none !important;
            }

            body {
                margin: 0;
                background: white;
            }

            .container {
                box-shadow: none;
                padding: 20px;
            }

            @page {
                margin: 1cm;
                size: auto;
            }
        }
    </style>
</head>
<body onload="window.print()">
<div class="container">
    <div class="header">
        <h2 class="h2 mb-0 text-decoration-none">{{ config('app.name') }}</h2>
        <h2>Lease Schedule Summary</h2>
        <p>Generated on {{ now()->format('d/m/Y') }}</p>
        <button class="btn-print no-print" onclick="window.print()">Print Again</button>
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
        <div class="form-value">{{ \Carbon\Carbon::parse($leaseschedule->StartDate)->format('d/m/Y') }}</div>
    </div>
    <div class="form-row">
        <div class="form-label">End Date</div>
        <div class="form-value">{{ \Carbon\Carbon::parse($leaseschedule->EndDate)->format('d/m/Y') }}</div>
    </div>
    <div class="form-row">
        <div class="form-label">Base Rent</div>
        <div class="form-value">KES {{ number_format($leaseschedule->BaseRent) }}</div>
    </div>
    <div class="form-row">
        <div class="form-label">Service Charge</div>
        <div class="form-value">KES {{ number_format($leaseschedule->ServiceCharge) }}</div>
    </div>
    <div class="form-row">
        <div class="form-label">Parking Fee</div>
        <div class="form-value">KES {{ number_format($leaseschedule->ParkingFee) }}</div>
    </div>
    <div class="form-row">
        <div class="form-label">Other Charges</div>
        <div class="form-value">KES {{ number_format($leaseschedule->OtherCharges) }}</div>
    </div>
</div>
</body>
</html>
