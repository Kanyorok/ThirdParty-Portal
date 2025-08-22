@extends('layouts.print')

@section('title', 'Policy Schedule')

@section('content')
    <div class="container mt-4" style="font-size: 14px;">
        <h4 class="text-center mb-4">📄 Policy Schedule</h4>

        <table class="table table-bordered">
            <tr>
                <th>Policy Number:</th>
                <td>POL-202507001</td>
                <th>Status:</th>
                <td>Active</td>
            </tr>
            <tr>
                <th>Client Name:</th>
                <td>Jane Njeri</td>
                <th>ID/Passport:</th>
                <td>12345678</td>
            </tr>
            <tr>
                <th>Product:</th>
                <td>Credit Life</td>
                <th>Policy Type:</th>
                <td>Life Insurance</td>
            </tr>
            <tr>
                <th>Sum Assured:</th>
                <td>KES 500,000</td>
                <th>Premium:</th>
                <td>KES 5,000</td>
            </tr>
            <tr>
                <th>Policy Start Date:</th>
                <td>2025-07-10</td>
                <th>Policy End Date:</th>
                <td>2026-07-10</td>
            </tr>
        </table>

        <h6 class="mt-4"><strong>Coverage Description:</strong></h6>
        <p>
            This Credit Life insurance policy covers outstanding loan balances in case of death or total permanent
            disability.
        </p>

        <h6 class="mt-4"><strong>Additional Notes:</strong></h6>
        <p>
            Includes standard exclusions such as suicide within the first year, fraud, or non-disclosure.
        </p>

        <div class="mt-5 text-end">
            <small>Generated on: {{ \Carbon\Carbon::now()->format('d M Y') }}</small>
        </div>
    </div>
@endsection
