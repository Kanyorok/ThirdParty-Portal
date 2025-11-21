@php use Carbon\Carbon; @endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Lease Offer Letter</title>

    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            margin: 45px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid #444;
        }

        .header h2 {
            margin: 0;
            font-size: 24px;
            letter-spacing: 0.5px;
        }

        .date-section {
            text-align: right;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .section-title {
            font-weight: bold;
            font-size: 16px;
            margin-top: 25px;
            color: #222;
            text-decoration: underline;
        }

        .content p {
            font-size: 15px;
            line-height: 1.6;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 20px;
        }

        table th, table td {
            border: 1px solid #aaa;
            padding: 8px;
            font-size: 14px;
        }

        table th {
            background: #f2f2f2;
            font-weight: bold;
        }

        .signature-section {
            margin-top: 50px;
        }

        .signature-block {
            width: 45%;
            display: inline-block;
            vertical-align: top;
            text-align: left;
        }

        .signature-line {
            margin-top: 55px;
            border-top: 1px solid #000;
            width: 80%;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <div class="header">
        <h2>Lease Offer Letter</h2>
    </div>

    {{-- DATE --}}
    <div class="date-section">
        <strong>Date:</strong> {{ Carbon::now()->format('d/m/Y') }}
    </div>

    {{-- RECIPIENT DETAILS --}}
    <div class="content">
        <p><strong>To:</strong> {{ $lease->tenant->thirdParty->ThirdPartyName ?? '-' }}<br>
        <strong>Subject Property:</strong> 
            {{ $lease->property->PropertyName ?? '-' }} - 
            Block {{ $lease->block->BlockName ?? '-' }}, 
            Floor {{ $lease->floor->FloorLabel ?? '-' }}, 
            Unit {{ $lease->unit->UnitCode ?? '-' }}
        </p>

        <p>Dear <strong>{{ $lease->tenant->thirdParty->ThirdPartyName ?? '-' }}</strong>,</p>

        <p>
            We are pleased to formally extend to you an offer to lease the above-mentioned unit under the following terms and conditions. This offer outlines the financial obligations, lease duration, and other relevant terms governing the tenancy.
        </p>

        {{-- LEASE TERMS --}}
        <h4 class="section-title">Lease Terms & Financial Summary</h4>

        <table>
            <tr>
                <th>Description</th>
                <th>Amount / Details</th>
            </tr>
            <tr>
                <td>Monthly Rent</td>
                <td>{{ number_format($lease->MonthlyRent, 2) }}</td>
            </tr>
            <tr>
                <td>Security Deposit</td>
                <td>{{ number_format($lease->Deposit, 2) }}</td>
            </tr>
            <tr>
                <td>Service Charge</td>
                <td>{{ number_format($lease->ServiceCharge, 2) }}</td>
            </tr>
            <tr>
                <td>Parking Fee</td>
                <td>{{ number_format($lease->ParkingFee, 2) }}</td>
            </tr>
            <tr>
                <td>Other Charges</td>
                <td>{{ number_format($lease->OtherCharges, 2) }}</td>
            </tr>
            <tr>
                <td>Payment Frequency</td>
                <td>{{ $lease->code->Description ?? '-' }}</td>
            </tr>
            <tr>
                <td>Due Day</td>
                <td>{{ $lease->DueDay }}</td>
            </tr>
            <tr>
                <td>Lease Duration</td>
                <td>
                    {{ Carbon::parse($lease->StartDate)->format('d/m/Y') }}
                    –
                    {{ Carbon::parse($lease->EndDate)->format('d/m/Y') }}
                </td>
            </tr>
        </table>

        {{-- SPECIAL TERMS --}}
        <h4 class="section-title">Special Terms</h4>

        <p>
            {!! nl2br(e($lease->SpecialTerms ?? 'No special terms provided.')) !!}
        </p>

        {{-- NOTICE --}}
        <p>
            Kindly review the terms outlined above. If acceptable, please sign in the space provided below and return the signed copy to our office. Upon acceptance, a formal lease agreement will be issued for execution.
        </p>

        <p>We look forward to having you as a valued tenant.</p>

        <p>Yours faithfully,<br>
        <strong>Property Management Team</strong></p>

        {{-- SIGNATURES --}}
        <div class="signature-section">

            <div class="signature-block">
                <strong>______________________________</strong><br>
                <span>Tenant Signature</span><br>
                <strong>Name:</strong> {{ $lease->tenant->thirdParty->ThirdPartyName ?? '-' }}<br>
                <strong>Date:</strong> __________________
            </div>

            <div class="signature-block" style="float:right;">
                <strong>______________________________</strong><br>
                <span>Property Manager Signature</span><br>
                <strong>Name:</strong> __________________________<br>
                <strong>Date:</strong> __________________
            </div>

        </div>

    </div>

    <div class="footer">
        <em>This document is system-generated and does not require a physical company stamp.</em>
    </div>

</body>
</html>
