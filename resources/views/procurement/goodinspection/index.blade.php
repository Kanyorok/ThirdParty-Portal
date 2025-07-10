@extends('layouts.app')
@section('title', 'Inspection Reports')

@section('content')
    <div class="container mt-4">
        <div class="mb-2 d-flex justify-content-between">
            <a href="{{ route('goodsinspection.create') }}" class="btn btn-success">➕ New Inspection</a>
        </div>
        <h4 class="mb-3">🔍 Inspection Reports</h4>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Inspection ID</th>
                <th>Delivery Note</th>
                <th>Inspected By</th>
                <th>Date</th>
                <th>Overall Status</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>INSP-001</td>
                <td>DN-001</td>
                <td>John Mugo</td>
                <td>2025-07-02</td>
                <td><span class="badge bg-success">Accepted</span></td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection
