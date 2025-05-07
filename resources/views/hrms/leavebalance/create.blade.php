@extends('layouts.app')
@section('title', 'Leave Balance')
@section('content')  
<div class="container mt-5">
    <h2>Assign Leave Balance</h2>

    <form>
        <div class="form-group">
            <label for="emp_id">Employee ID</label>
            <input type="text" class="form-control" id="emp_id" value="EMP005">
        </div>

        <div class="form-group">
            <label for="emp_name">Employee Name</label>
            <input type="text" class="form-control" id="emp_name" value="Samuel Kariuki">
        </div>

        <div class="form-group">
            <label for="leave_type">Leave Type</label>
            <select class="form-control" id="leave_type">
                <option>Annual Leave</option>
                <option>Sick Leave</option>
                <option>Maternity Leave</option>
                <option>Emergency Leave</option>
                <option>Unpaid Leave</option>
            </select>
        </div>

        <div class="form-group">
            <label for="total_days">Total Leave Days</label>
            <input type="number" class="form-control" id="total_days" value="21">
        </div>

        <div class="form-group">
            <label for="used_days">Days Already Used</label>
            <input type="number" class="form-control" id="used_days" value="5">
        </div>

        <div class="form-group">
            <label for="balance">Remaining Balance</label>
            <input type="number" class="form-control" id="balance" value="16" readonly>
        </div>

        <button type="submit" class="btn btn-primary">Save Leave Balance</button>
        <button type="reset" class="btn btn-secondary ml-2">Reset</button>
    </form>
</div>
@endsection