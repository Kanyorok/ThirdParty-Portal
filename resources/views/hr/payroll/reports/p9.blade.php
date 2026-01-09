<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>P9 Tax Card</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            color: #111;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        .title {
            font-size: 15px;
            font-weight: 700;
        }
        .muted {
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 5px 6px;
            border: 1px solid #ddd;
        }
        th {
            background: #f5f5f5;
            text-align: left;
        }
        .text-end {
            text-align: right;
        }
        .no-border td {
            border: none;
            padding: 2px 0;
        }
    </style>
</head>
<body>
<div class="header">
    <div>
        <div class="title">P9 Tax Card</div>
        <div class="muted">Year: {{ $year }}</div>
    </div>
    <div class="text-end">
        <div class="title">{{ $company?->BankName ?? 'Organization' }}</div>
        @if(!empty($company?->BankRegNumber))
            <div class="muted">Employer PIN: {{ $company->BankRegNumber }}</div>
        @endif
        <div class="muted">{{ $company?->Address1 ?? '' }}</div>
    </div>
</div>

<table class="no-border" style="margin-bottom: 10px;">
    <tr>
        <td><strong>Employee:</strong> {{ trim($employee->FirstName.' '.$employee->LastName) }}</td>
        <td><strong>Employee No:</strong> {{ $employee->EmployeeNo }}</td>
        <td><strong>KRA PIN:</strong> {{ $employee->KRAPIN ?? '-' }}</td>
    </tr>
    <tr>
        <td><strong>Department:</strong> {{ $employee->department?->Name ?? '-' }}</td>
        <td><strong>Branch:</strong> {{ $employee->branch?->Name ?? '-' }}</td>
        <td><strong>Grade:</strong> {{ $employee->grade?->Name ?? '-' }}</td>
    </tr>
</table>

<table>
    <thead>
    <tr>
        <th>Month</th>
        <th class="text-end">Basic Pay</th>
        <th class="text-end">Taxable Allowances</th>
        <th class="text-end">Taxable Pay</th>
        <th class="text-end">Tax Charged</th>
        <th class="text-end">Personal Relief</th>
        <th class="text-end">PAYE Tax</th>
        <th class="text-end">Net Pay</th>
    </tr>
    </thead>
    <tbody>
    @foreach($rows as $row)
        <tr>
            <td>{{ $row['Month'] }}</td>
            <td class="text-end">{{ number_format((float)$row['Basic'], 2) }}</td>
            <td class="text-end">{{ number_format((float)$row['TaxableAllowances'], 2) }}</td>
            <td class="text-end">{{ number_format((float)$row['TaxablePay'], 2) }}</td>
            <td class="text-end">{{ number_format((float)$row['TaxCharged'], 2) }}</td>
            <td class="text-end">{{ number_format((float)$row['PersonalRelief'], 2) }}</td>
            <td class="text-end">{{ number_format((float)$row['PAYE'], 2) }}</td>
            <td class="text-end">{{ number_format((float)$row['Net'], 2) }}</td>
        </tr>
    @endforeach
    <tr>
        <th>Total</th>
        <th class="text-end">{{ number_format((float)$totals['Basic'], 2) }}</th>
        <th class="text-end">{{ number_format((float)$totals['TaxableAllowances'], 2) }}</th>
        <th class="text-end">{{ number_format((float)$totals['TaxablePay'], 2) }}</th>
        <th class="text-end">{{ number_format((float)$totals['TaxCharged'], 2) }}</th>
        <th class="text-end">{{ number_format((float)$totals['PersonalRelief'], 2) }}</th>
        <th class="text-end">{{ number_format((float)$totals['PAYE'], 2) }}</th>
        <th class="text-end">{{ number_format((float)$totals['Net'], 2) }}</th>
    </tr>
    </tbody>
</table>

<div style="margin-top: 18px;">
    <div>Employer Signature: ___________________________</div>
    <div style="margin-top: 6px;">Employee Signature: ___________________________</div>
</div>
</body>
</html>
