@extends('layouts.app')
@section('title', 'Licensing')
@section('content')
<div class="container mt-5">
    <h2>Insurance & Licensing Records</h2>
    <a href="{{ route('licensing.create') }}" class="btn btn-primary mb-3">Add New Record</a>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Vehicle</th>
                <th>Insurance Provider</th>
                <th>Policy Number</th>
                <th>Insurance Expiry</th>
                <th>License Expiry</th>
                <th>Status</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Toyota Hilux - KDA 123A</td>
                <td>Jubilee Insurance</td>
                <td>INS-2025001</td>
                <td>2025-12-15</td>
                <td>2025-08-30</td>
                <td>Active</td>
                <td>Renew before August</td>
            </tr>
            <tr>
                <td>2</td>
                <td>Isuzu NQR - KCF 987B</td>
                <td>Britam</td>
                <td>INS-2025002</td>
                <td>2025-06-10</td>
                <td>2025-06-30</td>
                <td>Expiring Soon</td>
                <td>Start renewal process</td>
            </tr>
            <tr>
                <td>3</td>
                <td>Nissan Caravan - KDH 234C</td>
                <td>APA Insurance</td>
                <td>INS-2025003</td>
                <td>2025-03-20</td>
                <td>2025-04-15</td>
                <td>Expired</td>
                <td>Do not operate until renewed</td>
            </tr>
        </tbody>
    </table>
</div>

@endsection