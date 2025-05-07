@extends('layouts.app')
@section('title', 'Payroll Settings')
@section('content')

<div class="container mt-5">
        <h1 class="mb-4">Payroll Settings</h1>

        <!-- Salary Structure -->
        <h2 class="mb-4">Salary Structure</h2>
        <?php
            $salaryComponents = [
                "Basic Salary" => 40000,
                "House Allowance" => 10000,
                "Transport Allowance" => 5000
            ];
        ?>
        <table class="table table-bordered mb-4">
            <thead>
                <tr>
                    <th>Component</th>
                    <th>Amount (KES)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($salaryComponents as $component => $amount): ?>
                    <tr>
                        <td><?= $component ?></td>
                        <td><?= number_format($amount, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Allowances & Deductions -->
        <h2 class="mb-4">Allowances & Deductions</h2>
        <?php
            $allowances = [
                "Medical Allowance" => 3000,
                "Overtime" => 2000
            ];

            $deductions = [
                "NSSF" => 1080,
                "NHIF" => 1700,
                "PAYE" => 2500
            ];
        ?>

        <h3>Allowances</h3>
        <table class="table table-bordered mb-4">
            <thead>
                <tr>
                    <th>Allowance</th>
                    <th>Amount (KES)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allowances as $name => $value): ?>
                    <tr>
                        <td><?= $name ?></td>
                        <td><?= number_format($value, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Deductions</h3>
        <table class="table table-bordered mb-4">
            <thead>
                <tr>
                    <th>Deductions</th>
                    <th>Amount (KES)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deductions as $name => $value): ?>
                    <tr>
                        <td><?= $name ?></td>
                        <td><?= number_format($value, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Tax Brackets -->
        <h2 class="mb-4">Tax Brackets (Kenya PAYE Example)</h2>
        <?php
            $taxBrackets = [
                ["min" => 0, "max" => 24000, "rate" => 10],
                ["min" => 24001, "max" => 32333, "rate" => 25],
                ["min" => 32334, "max" => null, "rate" => 30]
            ];
        ?>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Income Range (KES)</th>
                    <th>Rate (%)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($taxBrackets as $bracket): ?>
                    <tr>
                        <td>
                            KES <?= number_format($bracket["min"]) ?> 
                            - 
                            <?= $bracket["max"] ? "KES " . number_format($bracket["max"]) : "Above" ?>
                        </td>
                        <td><?= $bracket["rate"] ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

@endsection