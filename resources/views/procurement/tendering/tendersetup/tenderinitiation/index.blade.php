@extends('layouts.app')
@section('title', 'New Tender')
@section('content')
        <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Initiated Tenders</h4>
     <a href="{{ route('initiatetender.create') }}" class="btn btn-sm btn-success">+ New Tender</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>PR No.</th>
                    <th>Deadline</th>
                    <th>Opening Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Sample row -->
                <tr>
                    <td>1</td>
                    <td>Supply of ICT Equipment</td>
                    <td><span class="badge bg-warning">Restricted</span></td>
                    <td>Goods</td>
                    <td>PR/2025/001</td>
                    <td>2025-06-10</td>
                    <td>2025-06-11</td>
                    <td><span class="badge bg-primary">Draft</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-info">View</button>
                        <button class="btn btn-sm btn-outline-success">Edit</button>
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection