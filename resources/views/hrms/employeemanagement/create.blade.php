@extends('layouts.app')
@section('title', 'Employee Management')
@section('content')
<div class="container mt-5">
    <h2>Add New Employee</h2>

    <form>
        <div class="form-group">
            <label for="empName">Full Name</label>
            <input type="text" class="form-control" id="empName" value="Jane Doe">
        </div>

        <div class="form-group">
            <label for="empID">Employee ID</label>
            <input type="text" class="form-control" id="empID" value="EMP00123">
        </div>

        <div class="form-group">
            <label for="jobTitle">Job Title</label>
            <input type="text" class="form-control" id="jobTitle" value="Fleet Supervisor">
        </div>

        <div class="form-group">
            <label for="department">Department</label>
            <select class="form-control" id="department">
                <option selected>Fleet Management</option>
                <option>Maintenance</option>
                <option>Logistics</option>
                <option>HR</option>
                <option>IT</option>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="phone">Phone</label>
                <input type="text" class="form-control" id="phone" value="+254712345678">
            </div>
            <div class="form-group col-md-6">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" value="jane.doe@example.com">
            </div>
        </div>

        <div class="form-group">
            <label for="status">Employment Status</label>
            <select class="form-control" id="status">
                <option selected>Active</option>
                <option>On Leave</option>
                <option>Terminated</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Save Employee</button>
        <a href="index_employee.php" class="btn btn-secondary ml-2">Cancel</a>
    </form>
</div>
@endsection