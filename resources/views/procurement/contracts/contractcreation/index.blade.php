@extends('layouts.app')
@section('title', 'Procurement Contracts')
@section('content')
    <div class="container mt-4">
        <h4 class="mb-3">📜 Procurement Contracts</h4>

        <!-- Add Contract Button -->
        <div class="mb-3 text-end">
            <a href="{{ route('contracts.create') }}" class="btn btn-primary">➕ New Contract</a>
        </div>

        <!-- Contracts Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Contract Ref</th>
                            <th>Title</th>
                            <th>Supplier</th>
                            <th>Status</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>1</td>
                            <td>CONTRACT/PROC/2025/009</td>
                            <td>Supply of Office Furniture</td>
                            <td>OfficePro Suppliers Ltd</td>
                            <td><span class="badge bg-success">Signed</span></td>
                            <td>2025-07-01</td>
                            <td>2025-12-31</td>
                            <td class="text-center">
                                <a href="{{ route('contracts.show', 1) }}"
                                   class="btn btn-sm btn-outline-secondary">View</a>
                                <a href="{{ route('contracts.edit', 1) }}"
                                   class="btn btn-sm btn-outline-primary">Edit</a>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection


