@extends('layouts.app')
@section('title', 'Leave Requests')
@section('content')

<div class="container mt-5">
    <h2>Submit Leave Request</h2>

    <form>
        <div class="form-group">
            <label for="employeeID">Employee ID</label>
            <input type="text" class="form-control" id="employeeID" value="EMP1003">
        </div>

        <div class="form-group">
            <label for="employeeName">Employee Name</label>
            <input type="text" class="form-control" id="employeeName" value="Grace Wambui">
        </div>

        <div class="form-group">
            <label for="leaveType">Leave Type</label>
            <select class="form-control" id="leaveType">
                <option selected>Annual Leave</option>
                <option>Sick Leave</option>
                <option>Maternity Leave</option>
                <option>Emergency Leave</option>
                <option>Study Leave</option>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="startDate">Start Date</label>
                <input type="date" class="form-control" id="startDate" value="2025-06-01">
            </div>
            <div class="form-group col-md-6">
                <label for="endDate">End Date</label>
                <input type="date" class="form-control" id="endDate" value="2025-06-14">
            </div>
        </div>

        <div class="form-group">
            <label for="reason">Reason</label>
            <textarea class="form-control" id="reason" rows="3">Family commitment</textarea>
        </div>

        <button type="submit" class="btn btn-success">Submit Leave Request</button>
        <a href="index_leave_requests.php" class="btn btn-secondary ml-2">Cancel</a>
    </form>
</div>
@endsection
