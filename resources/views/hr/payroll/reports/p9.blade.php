<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>P9 Tax Card</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 6mm 8mm 4mm;
        }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 8px;
            color: #111;
            margin: 0;
        }
        .kra-header {
            text-align: center;
            margin-bottom: 6px;
        }
        .kra-title {
            font-size: 12px;
            font-weight: 700;
        }
        .muted {
            color: #666;
        }
        .meta {
            margin-bottom: 8px;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 3px 4px;
            border: 1px solid #bbb;
        }
        th {
            background: #f0f0f0;
            text-align: center;
            vertical-align: middle;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .code {
            display: block;
            font-size: 8px;
            color: #444;
        }
        .signatures {
            margin-top: 14px;
        }
        .p9-scale {
            transform: scale(0.95);
            transform-origin: left top;
        }
    </style>
</head>
<body>
<div class="kra-header">
    <div class="muted">APPENDIX 2A</div>
    <div class="kra-title">KENYA REVENUE AUTHORITY</div>
    <div class="muted">DOMESTIC TAXES DEPARTMENT</div>
    <div class="muted">TAX DEDUCTION CARD YEAR {{ $year }}</div>
</div>

@php
    $mainName = $employee->LastName ?? '';
    $otherNames = trim(($employee->FirstName ?? '').' '.($employee->OtherNames ?? ''));
@endphp
<div class="meta">
    <div class="meta-row">
        <div><strong>Employer's Name:</strong> {{ $company?->BankName ?? 'Organization' }}</div>
        <div><strong>Employer's PIN:</strong> {{ $company?->EmployerTaxPIN ?? $company?->BankRegNumber ?? '-' }}</div>
    </div>
    <div class="meta-row">
        <div><strong>Employee's Main Name:</strong> {{ $mainName ?: '-' }}</div>
        <div><strong>Employee's PIN:</strong> {{ $employee->KRAPIN ?? '-' }}</div>
    </div>
    <div class="meta-row">
        <div><strong>Employee's Other Names:</strong> {{ $otherNames ?: '-' }}</div>
        <div><strong>Employee No:</strong> {{ $employee->EmployeeNo ?? '-' }}</div>
    </div>
    <div class="meta-row">
        <div><strong>Department:</strong> {{ $employee->department?->Name ?? '-' }}</div>
        <div><strong>Branch:</strong> {{ $employee->branch?->Name ?? '-' }}</div>
    </div>
</div>

<div class="p9-scale">
<table>
    <thead>
    <tr>
        <th rowspan="2">Month</th>
        <th rowspan="2">Basic Salary<span class="code">A</span></th>
        <th rowspan="2">Benefits - Non Cash<span class="code">B</span></th>
        <th rowspan="2">Value of Quarters<span class="code">C</span></th>
        <th rowspan="2">Total Gross Pay<span class="code">D</span></th>
        <th colspan="3">Defined Contribution Retirement Scheme<span class="code">E</span></th>
        <th rowspan="2">Affordable Housing Levy (AHL)<span class="code">F</span></th>
        <th rowspan="2">Social Health Insurance Fund (SHIF)<span class="code">G</span></th>
        <th rowspan="2">Post Retirement Medical Fund (PRMF)<span class="code">H</span></th>
        <th rowspan="2">Owner-Occupied Interest<span class="code">I</span></th>
        <th rowspan="2">Total Deductions (E+F+G+H+I)<span class="code">J</span></th>
        <th rowspan="2">Chargeable Pay (D-J)<span class="code">K</span></th>
        <th rowspan="2">Tax Charged<span class="code">L</span></th>
        <th rowspan="2">Personal Relief<span class="code">M</span></th>
        <th rowspan="2">Insurance Relief<span class="code">N</span></th>
        <th rowspan="2">PAYE Tax (L-M-N)<span class="code">O</span></th>
    </tr>
    <tr>
        <th>E1 30% of A</th>
        <th>E2 Actual</th>
        <th>E3 Fixed 30,000 p.m.</th>
    </tr>
    </thead>
    <tbody>
    @foreach($rows as $row)
        <tr>
            <td>{{ $row['Month'] }}</td>
            <td class="text-end">{{ number_format((float)$row['Basic'], 2) }}</td>
            <td class="text-end">{{ number_format((float)$row['BenefitsNonCash'], 2) }}</td>
            <td class="text-end">{{ number_format((float)$row['ValueOfQuarters'], 2) }}</td>
            <td class="text-end">{{ number_format((float)$row['GrossPay'], 2) }}</td>
            <td class="text-end">{{ number_format((float)$row['PensionE1'], 2) }}</td>
            <td class="text-end">{{ number_format((float)$row['PensionE2'], 2) }}</td>
            <td class="text-end">{{ number_format((float)$row['PensionE3'], 2) }}</td>
            <td class="text-end">{{ number_format((float)$row['HousingLevy'], 2) }}</td>
            <td class="text-end">{{ number_format((float)$row['SHIF'], 2) }}</td>
            <td class="text-end">{{ number_format((float)$row['PRMF'], 2) }}</td>
            <td class="text-end">{{ number_format((float)$row['OwnerInterest'], 2) }}</td>
            <td class="text-end">{{ number_format((float)$row['TotalDeductions'], 2) }}</td>
            <td class="text-end">{{ number_format((float)$row['ChargeablePay'], 2) }}</td>
            <td class="text-end">{{ number_format((float)$row['TaxCharged'], 2) }}</td>
            <td class="text-end">{{ number_format((float)$row['PersonalRelief'], 2) }}</td>
            <td class="text-end">{{ number_format((float)$row['InsuranceRelief'], 2) }}</td>
            <td class="text-end">{{ number_format((float)$row['PAYE'], 2) }}</td>
        </tr>
    @endforeach
    <tr>
        <th>Total</th>
        <th class="text-end">{{ number_format((float)$totals['Basic'], 2) }}</th>
        <th class="text-end">{{ number_format((float)$totals['BenefitsNonCash'], 2) }}</th>
        <th class="text-end">{{ number_format((float)$totals['ValueOfQuarters'], 2) }}</th>
        <th class="text-end">{{ number_format((float)$totals['GrossPay'], 2) }}</th>
        <th class="text-end">{{ number_format((float)$totals['PensionE1'], 2) }}</th>
        <th class="text-end">{{ number_format((float)$totals['PensionE2'], 2) }}</th>
        <th class="text-end">{{ number_format((float)$totals['PensionE3'], 2) }}</th>
        <th class="text-end">{{ number_format((float)$totals['HousingLevy'], 2) }}</th>
        <th class="text-end">{{ number_format((float)$totals['SHIF'], 2) }}</th>
        <th class="text-end">{{ number_format((float)$totals['PRMF'], 2) }}</th>
        <th class="text-end">{{ number_format((float)$totals['OwnerInterest'], 2) }}</th>
        <th class="text-end">{{ number_format((float)$totals['TotalDeductions'], 2) }}</th>
        <th class="text-end">{{ number_format((float)$totals['ChargeablePay'], 2) }}</th>
        <th class="text-end">{{ number_format((float)$totals['TaxCharged'], 2) }}</th>
        <th class="text-end">{{ number_format((float)$totals['PersonalRelief'], 2) }}</th>
        <th class="text-end">{{ number_format((float)$totals['InsuranceRelief'], 2) }}</th>
        <th class="text-end">{{ number_format((float)$totals['PAYE'], 2) }}</th>
    </tr>
    </tbody>
</table>
</div>

<div class="signatures">
    <div><strong>Total Chargeable Pay (Col K):</strong> {{ number_format((float)$totals['ChargeablePay'], 2) }}</div>
    <div><strong>Total Tax Charged (Col L):</strong> {{ number_format((float)$totals['TaxCharged'], 2) }}</div>
    <div><strong>Total PAYE (Col O):</strong> {{ number_format((float)$totals['PAYE'], 2) }}</div>
</div>

<div class="signatures">
    <div>Employer Signature: ___________________________</div>
    <div style="margin-top: 6px;">Employee Signature: ___________________________</div>
</div>
</body>
</html>
