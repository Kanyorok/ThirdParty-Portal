@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Tender Types</h4>
        <a href="{{ route('tendertype.create') }}" class="btn btn-sm btn-success">+ New Type</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Type Code</th>
                    <th>Tender Type</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Sample Row -->
                <tr>
                    <td>1</td>
                    <td>TYP-001</td>
                    <td>Open</td>
                    <td>Tenders available to all qualified suppliers for competitive bidding.</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary">Edit</button>
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </td>
                </tr>
                <!-- Additional rows -->
            </tbody>
        </table>
    </div>
</div>

@endsection