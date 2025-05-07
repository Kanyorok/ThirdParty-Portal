@extends('layouts.app')
@section('title', 'Add Driver ')
@section('content')

<div class="container mt-5">
    <h2 class="mb-3">Fleet Management Module</h2>

    <div class="mb-4">
        <label><strong>Section:</strong> Driver Management</label><br>
        <label><strong>Function:</strong> Maintain records for all assigned drivers</label>
    </div>

    <h4 class="mb-3">Add New Driver</h4>
    <form>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="driver_id">Driver ID</label>
                <input type="text" class="form-control" id="driver_id" value="DRV004">
            </div>
            <div class="form-group col-md-3">
                <label for="full_name">Full Name</label>
                <input type="text" class="form-control" id="full_name" value="Susan Nduta">
            </div>
            <div class="form-group col-md-3">
                <label for="license_number">License Number</label>
                <input type="text" class="form-control" id="license_number" value="DLK987321">
            </div>
            <div class="form-group col-md-3">
                <label for="expiry_date">License Expiry</label>
                <input type="date" class="form-control" id="expiry_date" value="2027-11-01">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="contact">Phone</label>
                <input type="text" class="form-control" id="contact" value="+254701234567">
            </div>
            <div class="form-group col-md-3">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" value="susan.nduta@example.com">
            </div>
            <div class="form-group col-md-3">
                <label for="status">Employment Status</label>
                <select class="form-control" id="status">
                    <option selected>Active</option>
                    <option>On Leave</option>
                    <option>Suspended</option>
                    <option>Retired</option>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label for="remarks">Remarks</label>
                <input type="text" class="form-control" id="remarks" value="Newly assigned to Nairobi branch">
            </div>
        </div>
        <button type="submit" class="btn btn-success">➕ Save Driver</button>
        <button type="reset" class="btn btn-secondary ml-2">Clear</button>
    </form>

    <hr class="my-5">

    <h4 class="mb-3">Driver List</h4>
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Driver ID</th>
                <th>Name</th>
                <th>License Number</th>
                <th>Expiry</th>
                <th>Status</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>DRV001</td>
                <td>James Njoroge</td>
                <td>DLK123456</td>
                <td>2026-12-31</td>
                <td><span class="badge badge-success">Active</span></td>
                <td>+254712345678</td>
                <td>
                    <button class="btn btn-sm btn-info">View</button>
                    <button class="btn btn-sm btn-warning">Edit</button>
                </td>
            </tr>
            <tr>
                <td>DRV002</td>
                <td>Mary Wambui</td>
                <td>DLK654321</td>
                <td>2025-08-15</td>
                <td><span class="badge badge-secondary">On Leave</span></td>
                <td>+254700112233</td>
                <td>
                    <button class="btn btn-sm btn-info">View</button>
                    <button class="btn btn-sm btn-warning">Edit</button>
                </td>
            </tr>
            <tr>
                <td>DRV003</td>
                <td>Peter Otieno</td>
                <td>DLK789456</td>
                <td>2027-03-10</td>
                <td><span class="badge badge-success">Active</span></td>
                <td>+254799876543</td>
                <td>
                    <button class="btn btn-sm btn-info">View</button>
                    <button class="btn btn-sm btn-warning">Edit</button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection