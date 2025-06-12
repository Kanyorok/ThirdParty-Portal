@extends('layouts.app')
@section('title', 'Suppliers List')
@section('content')

    <div class="card mb-4">
        <div class="mb-2 d-flex justify-content-between">
            <a href="{{ route('supplierslist.create') }}" class="btn btn-success">➕ Add Supplier</a>
        </div>
        <div class="card-header bg-secondary text-white">📋 Suppliers Directory</div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Supplier Name</th>
                    <th>Tax PIN</th>
                    <th>Business Type</th>
                    <th>Status</th>
                    <th>Approval</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>1</td>
                    <td>Zenta Supplies Ltd</td>
                    <td>P051478921D</td>
                    <td>Company</td>
                    <td>Active</td>
                    <td>Approved</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-info">Edit</a>
                        <a href="#" class="btn btn-sm btn-primary">View</a>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Makibu Holdings</td>
                    <td>A093182763X</td>
                    <td>Individual</td>
                    <td>Pending</td>
                    <td>Under Review</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-info">Edit</a>
                        <a href="#" class="btn btn-sm btn-primary">View</a>
                    </td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Jomak Technologies</td>
                    <td>K012345678Z</td>
                    <td>Company</td>
                    <td>Suspended</td>
                    <td>Approved</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-info">Edit</a>
                        <a href="#" class="btn btn-sm btn-primary">View</a>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

@endsection
