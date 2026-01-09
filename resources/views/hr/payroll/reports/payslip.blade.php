<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #111;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }
        .title {
            font-size: 16px;
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
            padding: 6px 8px;
            border: 1px solid #ddd;
        }
        th {
            background: #f5f5f5;
            text-align: left;
        }
        .no-border td {
            border: none;
            padding: 2px 0;
        }
        .text-end {
            text-align: right;
        }
        .section {
            margin-top: 10px;
        }
        .section-title {
            font-weight: 700;
            margin-bottom: 6px;
        }
    </style>
</head>
<body>
@php
    $month = (int)($run->cycle?->Month ?? now()->month);
    $year = (int)($run->cycle?->Year ?? now()->year);
    $periodLabel = \Carbon\Carbon::create($year, $month, 1)->format('F Y');
@endphp
<div class="header">
    <div>
        <div class="title">{{ $company?->BankName ?? 'Organization' }}</div>
        <div class="muted">{{ $company?->Address1 ?? '' }}</div>
        @if(!empty($company?->Address2))
            <div class="muted">{{ $company->Address2 }}</div>
        @endif
        @if(!empty($company?->Phone1))
            <div class="muted">Tel: {{ $company->Phone1 }}</div>
        @endif
        @if(!empty($company?->EmailID))
            <div class="muted">Email: {{ $company->EmailID }}</div>
        @endif
    </div>
    <div class="text-end">
        <div class="title">Payslip</div>
        <div class="muted">Period: {{ $periodLabel }}</div>
        <div class="muted">Generated: {{ now()->format('Y-m-d') }}</div>
    </div>
</div>

<table class="no-border" style="margin-bottom: 10px;">
    <tr>
        <td><strong>Employee:</strong> {{ trim($employee->FirstName.' '.$employee->LastName) }}</td>
        <td><strong>Employee No:</strong> {{ $employee->EmployeeNo }}</td>
        <td><strong>Department:</strong> {{ $employee->department?->Name ?? '-' }}</td>
        <td><strong>Branch:</strong> {{ $employee->branch?->Name ?? '-' }}</td>
    </tr>
    <tr>
        <td><strong>Grade:</strong> {{ $employee->grade?->Name ?? '-' }}</td>
        <td><strong>KRA PIN:</strong> {{ $employee->KRAPIN ?? '-' }}</td>
        <td><strong>NSSF:</strong> {{ $employee->NSSFNo ?? '-' }}</td>
        <td><strong>NHIF/SHIF:</strong> {{ $employee->NHIFNo ?? '-' }}</td>
    </tr>
    <tr>
        <td><strong>Bank:</strong> {{ $employee->bank?->BankName ?? '-' }}</td>
        <td><strong>Branch:</strong> {{ $employee->bankBranch?->BranchName ?? '-' }}</td>
        <td><strong>Account:</strong> {{ $employee->BankAccount ?? '-' }}</td>
        <td></td>
    </tr>
</table>

<div class="section">
    <div class="section-title">Earnings</div>
    <table>
        <thead>
        <tr>
            <th>Description</th>
            <th class="text-end">Amount</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Basic Salary</td>
            <td class="text-end">{{ number_format((float)$line->BasicSalary, 2) }}</td>
        </tr>
        @foreach($allowances as $row)
            <tr>
                <td>{{ $row->Name }}</td>
                <td class="text-end">{{ number_format((float)$row->Amount, 2) }}</td>
            </tr>
        @endforeach
        <tr>
            <td><strong>Total Earnings</strong></td>
            <td class="text-end"><strong>{{ number_format((float)($line->BasicSalary + $line->TotalAllowances), 2) }}</strong></td>
        </tr>
        </tbody>
    </table>
</div>

<div class="section">
    <div class="section-title">Deductions</div>
    <table>
        <thead>
        <tr>
            <th>Description</th>
            <th class="text-end">Amount</th>
        </tr>
        </thead>
        <tbody>
        @forelse($deductions as $row)
            <tr>
                <td>{{ $row->Name }}</td>
                <td class="text-end">{{ number_format((float)$row->Amount, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="2" class="muted">No deductions.</td>
            </tr>
        @endforelse
        <tr>
            <td><strong>Total Deductions</strong></td>
            <td class="text-end"><strong>{{ number_format((float)$line->TotalDeductions, 2) }}</strong></td>
        </tr>
        </tbody>
    </table>
</div>

<div class="section">
    <table>
        <tbody>
        <tr>
            <th>Net Pay</th>
            <th class="text-end">{{ number_format((float)$line->NetPay, 2) }}</th>
        </tr>
        </tbody>
    </table>
</div>
</body>
</html>
