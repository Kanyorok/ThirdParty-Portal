@extends('layouts.app')
@section('title', 'Supplier Evaluations')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Supplier Evaluations</h2>
        <a href="{{ route('evaluations.create') }}" class="btn btn-success">+ Create Evaluation</a>
    </div>

    <!-- Static Supplier Evaluation Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Committee Member</th>
                    <th>RFQ No</th>
                    <th>Supplier</th>
                    <th>Total Quoted</th>
                    <th>Delivery Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Static Data -->
                <tr>
                    <td>1</td>
                    <td>John Doe</td>
                    <td>RFQ001</td>
                    <td>Supplier A</td>
                    <td>$10,000</td>
                    <td>5 Days</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-primary">View</a>
                        <a href="#" class="btn btn-sm btn-warning">Edit</a>
                        <button class="btn btn-sm btn-danger" onclick="confirm('Are you sure?')">Delete</button>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Jane Smith</td>
                    <td>RFQ002</td>
                    <td>Supplier B</td>
                    <td>$9,500</td>
                    <td>7 Days</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-primary">View</a>
                        <a href="#" class="btn btn-sm btn-warning">Edit</a>
                        <button class="btn btn-sm btn-danger" onclick="confirm('Are you sure?')">Delete</button>
                    </td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Mary Wanjiku</td>
                    <td>RFQ003</td>
                    <td>Supplier C</td>
                    <td>$11,200</td>
                    <td>4 Days</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-primary">View</a>
                        <a href="#" class="btn btn-sm btn-warning">Edit</a>
                        <button class="btn btn-sm btn-danger" onclick="confirm('Are you sure?')">Delete</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
