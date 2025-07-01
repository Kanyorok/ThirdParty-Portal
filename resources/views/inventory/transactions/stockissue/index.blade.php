@extends('layouts.app')
@section('title', 'Issued Stock Log')
@section('content')

    <div class="card mb-4">

        <div class="mb-2 d-flex justify-content-between">
            <a href="{{ route('stockissue.create') }}" class="btn btn-success">➕ Issue Stock</a>
        </div>
        <div class="card-header bg-light">📦 Issued Stock Log</div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="table-secondary">
                <tr>
                    <th>#</th>
                    <th>Issue Ref</th>
                    <th>PR Ref</th>
                    <th>To Branch</th>
                    <th>Date</th>
                    <th>Issued By</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>1</td>
                    <td>ISS/2025/004</td>
                    <td>PR/2025/017</td>
                    <td>Branch A</td>
                    <td>2025-06-11</td>
                    <td>Peter K.</td>
                    <td>Dispatched</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-info">View</a>
                        <a href="#" class="btn btn-sm btn-secondary">Print</a>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

@endsection
