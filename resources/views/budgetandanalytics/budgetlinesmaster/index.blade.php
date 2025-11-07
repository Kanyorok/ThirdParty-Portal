@extends('layouts.app')
@section('title', 'Budget Item Master')
@section('content')
    <div class="card p-3">
        <h5 class="mb-3">📋 Budget Item Master</h5>

        <div class="mb-2 d-flex justify-content-between">
            <a href="{{ route('budgetitem.create') }}" class="btn btn-success">➕ Add New Item</a>
            <input type="search" class="form-control w-25" placeholder="🔍 Search..."/>
        </div>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Code</th>
                <th>Name</th>
                <th>Category</th>
                <th>CBS Linked</th>
                <th>GL Code</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>1</td>
                <td>BGT-001</td>
                <td>Loan Interest Income</td>
                <td>Revenue</td>
                <td>Yes</td>
                <td>GL-4001</td>
                <td>Active</td>
                <td>
                    <button class="btn btn-sm btn-primary">✏️ Edit</button>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

@endsection
