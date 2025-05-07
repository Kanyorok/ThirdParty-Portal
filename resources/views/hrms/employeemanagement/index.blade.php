@extends('layouts.app')
@section('title', 'Employee Management')
@section('content')
<div class="container mt-5">
    <h2>Employee Registry</h2>
    <a href="{{ route('employeemanagement.create') }}" class="btn btn-primary mb-3">Add New Employee</a>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Employee ID</th>
                <th>Full Name</th>
                <th>Job Title</th>
                <th>Department</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>EMP00123</td>
                <td>Jane Doe</td>
                <td>Fleet Supervisor</td>
                <td>Fleet Management</td>
                <td>+254712345678</td>
                <td>jane.doe@example.com</td>
                <td>Active</td>
            </tr>
            <tr>
                <td>2</td>
                <td>EMP00124</td>
                <td>John Mwangi</td>
                <td>Driver</td>
                <td>Logistics</td>
                <td>+254798765432</td>
                <td>john.mwangi@example.com</td>
                <td>On Leave</td>
            </tr>
            <tr>
                <td>3</td>
                <td>EMP00125</td>
                <td>Alice Wanjiku</td>
                <td>Mechanic</td>
                <td>Maintenance</td>
                <td>+254700112233</td>
                <td>alice.wanjiku@example.com</td>
                <td>Active</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection