@extends('layouts.app')
@section('title', 'Budget Projections')

@section('content')
    <div class="container my-3">
        <div class="card shadow-sm rounded-3 p-3">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 text-primary">📋</h5>
                <a href="{{ route('budgetprojections.create') }}" class="btn btn-info btn-sm px-3 py-2">
                    <i class="fas fa-plus me-1"></i> Add Projection
                </a>
            </div>

            <p class="text-muted mb-3">
                The table below displays forecasted financial values (projections) linked to products from the CBS.
                Each projection is based on specific drivers and rate types (e.g., interest or growth rates) and helps
                estimate expected income or expenses over a given period.
            </p>

            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle"
                       style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 13px;">
                    <thead class="table-light">
                    <tr class="text-start">
                        <th>#</th>
                        <th>Budget</th>
                        <th>Products</th>
                        <th>No. of Accounts</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($data as $item)
                        <tr class="text-start">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item['Name'] }}</td>
                            <td>{{ $item['Products'] }}</td>
                            <td>{{ $item['Accounts'] }}</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('budgetprojections.show', $item['Id']) }}"
                                       class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger custom-delete-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#customDeleteConfirmModal"
                                            data-name="{{ $item['Name'] }} (All Projections)"
                                            data-route="{{ route('budgetprojections.destroy', $item['Id']) }}"
                                            @if($item['ApprovalStatus'] === 'approved') disabled @endif
                                    >
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-0">
                                <div class="text-center p-4 border rounded-3 bg-light">
                                    <p class="mb-2 text-muted">
                                        <i class="fas fa-info-circle me-2 text-info"></i>
                                        No budget projections have been added yet.
                                    </p>
                                    <a href="{{ route('budgetprojections.create') }}" class="btn btn-info px-4 py-2">
                                        <i class="fas fa-plus-circle me-2"></i> Add Projection
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Shared Delete Modal --}}
    @include('components.modals.delete-confirm')
@endsection

@section('styles')
    <style>
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.2s ease;
        }
        .table th, .table td {
            text-align: left;
            vertical-align: middle;
            padding: 0.5rem;
        }
        .btn-sm {
            padding: 0.25rem 0.6rem;
            font-size: 0.8rem;
        }
        .card {
            border: none;
            border-radius: 0.5rem;
        }
    </style>
@endsection
