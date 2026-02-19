@extends('layouts.app')
@section('title', 'Leave Requests')
@section('content')  
<div class="container mt-5">
    <h2>Employee Leave Balances</h2>
    <a href="{{ route('leavebalance.create') }}" class="btn btn-primary mb-3">Assign Leave Balance</a>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Employee ID</th>
                <th>Name</th>
                <th>Leave Type</th>
                <th>Total Days</th>
                <th>Used Days</th>
                <th>Balance</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>EMP001</td>
                <td>John Doe</td>
                <td>Annual Leave</td>
                <td>30</td>
                <td>10</td>
                <td>20</td>
                <td>
                    <button class="btn btn-info btn-sm">View</button>
                    <button class="btn btn-warning btn-sm">Edit</button>
                </td>
            </tr>
            <tr>
                <td>EMP002</td>
                <td>Jane Wambui</td>
                <td>Sick Leave</td>
                <td>15</td>
                <td>3</td>
                <td>12</td>
                <td>
                    <button class="btn btn-info btn-sm">View</button>
                    <button class="btn btn-warning btn-sm">Edit</button>
                </td>
            </tr>
            <tr>
                <td>EMP003</td>
                <td>Peter Mwangi</td>
                <td>Emergency Leave</td>
                <td>10</td>
                <td>4</td>
                <td>6</td>
                <td>
                    <button class="btn btn-info btn-sm">View</button>
                    <button class="btn btn-warning btn-sm">Edit</button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection