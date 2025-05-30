@extends('layouts.app')
@section('title', 'Units of Measure (UOM)')
@section('content')
    <div class="container mt-4">

        <div class="mb-2 d-flex justify-content-between">
            <a href="{{ route('unitofmeasure.create') }}" class="btn btn-success">➕ Add UOM</a>

        </div>
        <h4>Unit of Measure (UOM)</h4>
        <table class="table table-bordered table-striped">
            <thead>
            <tr>
                <th>#</th>
                <th>Code</th>
                <th>Name</th>
                <th>Base Unit?</th>
                <th>Active</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <!-- Example Row -->
            <tr>
                <td>1</td>
                <td>PCS</td>
                <td>Pieces</td>
                <td>✔️</td>
                <td>✔️</td>
                <td>
                    <button class="btn btn-sm btn-info">Edit</button>
                    <button class="btn btn-sm btn-danger">Delete</button>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

@endsection
