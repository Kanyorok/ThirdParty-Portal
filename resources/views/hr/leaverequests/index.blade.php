@extends('layouts.app')
@section('title', 'Leave Requests')
@section('content')  
<div class="container mt-5">
    <h2>Leave Requests</h2>
    <a href="{{ route('leaverequests.create') }}" class="btn btn-primary mb-3">Create New Leave Request</a>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Employee ID</th>
                <th>Name</th>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        
            <tr>
                <td>EMP001</td>
                <td>John Doe</td>
                <td>Annual Leave</td>
                <td>2025-06-01</td>
                <td>2025-06-10</td>
                <td>Family Trip</td>
                <td>Pending</td>
                <td>
                    <button class="btn btn-info btn-sm">View</button>
                    <button class="btn btn-warning btn-sm">Edit</button>
                    <button class="btn btn-danger btn-sm">Cancel</button>
                </td>
            </tr>
            <tr>
                <td>EMP002</td>
                <td>Jane Wambui</td>
                <td>Sick Leave</td>
                <td>2025-05-15</td>
                <td>2025-05-20</td>
                <td>Medical treatment</td>
                <td>Approved</td>
                <td>
                    <button class="btn btn-info btn-sm">View</button>
                    <button class="btn btn-warning btn-sm" disabled>Edit</button>
                    <button class="btn btn-danger btn-sm" disabled>Cancel</button>
                </td>
            </tr>
            <tr>
                <td>EMP003</td>
                <td>Peter Mwangi</td>
                <td>Emergency Leave</td>
                <td>2025-04-28</td>
                <td>2025-04-30</td>
                <td>Urgent travel</td>
                <td>Rejected</td>
                <td>
                    <button class="btn btn-info btn-sm">View</button>
                    <button class="btn btn-warning btn-sm">Edit</button>
                    <button class="btn btn-danger btn-sm">Cancel</button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection