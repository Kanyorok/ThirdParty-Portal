@extends('layouts.app')
@section('title', 'Payroll Dashboard')
@section('content')

<div class="container mt-5">
    <h2 class="text-center mb-4">Payroll Management Dashboard</h2>

    <div class="table-responsive">
        <table class="table table-bordered table-striped text-center">
            <thead class="table-dark">
                <tr>
                    <th>Employee Name</th>
                    <th>Employee ID</th>
                    <th>Department</th>
                    <th>Monthly Salary</th>
                    <th>Base Pay</th>
                    <th>Bonus</th>
                    <th>Total Payable</th>
                    <th>Health Insurance</th>
                    <th>Pension</th>
                    <th>Total Deductions</th>
                    <th>Income Tax</th>
                    <th>Payslip Status</th>
                    <th>Pay Date</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Jane Doe</td>
                    <td>EMP12345</td>
                    <td>Finance</td>
                    <td>$5,000</td>
                    <td>$4,500</td>
                    <td>$250</td>
                    <td>$4,750</td>
                    <td>$100</td>
                    <td>$50</td>
                    <td>$150</td>
                    <td>$100</td>
                    <td>Generated</td>
                    <td>06-May-2025</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>



@endsection