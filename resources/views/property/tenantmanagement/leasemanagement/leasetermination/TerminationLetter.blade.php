@php use Carbon\Carbon; @endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Lease Termination Letter</title>

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

<div class="header">
    <h2>Lease Termination Notice</h2>
</div>

<div class="date-section">
    <strong>Date:</strong> {{ Carbon::now()->format('d/m/Y') }}
</div>

<div class="content">

    <p><strong>To:</strong> {{ $termination->lease->tenant->thirdParty->ThirdPartyName ?? '-' }}</p>

    <p><strong>Subject Property:</strong>
        {{ $termination->lease->property->PropertyName ?? '-' }} -
        Block {{ $termination->lease->block->BlockName ?? '-' }},
        Floor {{ $termination->lease->floor->FloorLabel ?? '-' }},
        Unit {{ $termination->lease->unit->UnitCode ?? '-' }}
    </p>

    <p>Dear <strong>{{ $termination->lease->tenant->thirdParty->ThirdPartyName ?? '-' }}</strong>,</p>

    <p>
        This letter serves as a formal notification that your lease for the above-mentioned premises
        has been terminated in accordance with your request or due to management decision.
        The termination details are outlined below.
    </p>

    <h4 class="section-title">Termination Summary</h4>

    <table>
        <tr>
            <th>Description</th>
            <th>Details</th>
        </tr>

        <tr>
            <td>Lease Number</td>
            <td>{{ $termination->lease->LeaseNumber }}</td>
        </tr>

        <tr>
            <td>Termination Date</td>
            <td>{{ Carbon::parse($termination->TerminationDate)->format('d/m/Y') }}</td>
        </tr>

        <tr>
            <td>Termination Reason</td>
            <td>{{ $termination->code->Description ?? '-' }}</td>
        </tr>

        <tr>
            <td>Remarks</td>
            <td>{{ $termination->Remarks ?: '-' }}</td>
        </tr>
    </table>

    <p>
        Kindly ensure that all outstanding balances are cleared and the unit is returned in good
        condition as per the lease agreement obligations. Any refundable deposits will be processed
        upon completion of exit inspection and clearance.
    </p>

    <p>
        Should you require any clarification, feel free to contact our office.
    </p>

    <p>We thank you for your tenancy.</p>

    <p>Yours faithfully,<br>
        <strong>Property Management Team</strong>
    </p>
</div>

<div class="footer">
    <em>This document is system-generated and does not require a physical company stamp.</em>
</div>

</body>
</html>
