@extends('layouts.app')
@section('title', 'Ledger Limits')

@section('content')
    <div class="container my-3">
        <!-- Card for Ledger Limits -->
        <div class="card shadow-sm rounded-3" style="margin: 0.5rem;">
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary">📊 Ledger Limits</h5>
                <form method="POST" action="#">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm p-2">
                        Update from Allocations
                    </button>
                </form>
            </div>
            <div class="card-body p-3">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @elseif(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Ledger Limits Table -->
                <div class="table-responsive mt-3">
                    <table class="table table-hover table-sm align-middle"
                           style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                        <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Budget Line</th>
                            <th scope="col">Ledger</th>
                            <th scope="col">Branch</th>
                            <th scope="col">Limit Type</th>
                            <th scope="col">Limit Amount</th>
                            <th scope="col">Effective From</th>
                            <th scope="col">Effective To</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($limits as $limit)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $limit->budgetLine->LineName }}</td>
                                <td>{{ $limit->ledger->AccountID ?? '-' }}</td>
                                <td>{{ $limit->branch->Name ?? 'HQ' }}</td>
                                <td>{{ $limit->LimitType }}</td>
                                <td>{{ number_format($limit->LimitAmount, 2) }}</td>
                                <td>{{ \Carbon\Carbon::parse($limit->EffectiveFrom)->format('Y-m-d') }}</td>
                                <td>{{ $limit->EffectiveTo ? \Carbon\Carbon::parse($limit->EffectiveTo)->format('Y-m-d') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-0">
                                    <div class="text-center p-4 border rounded-3 bg-light">
                                        <p class="mb-3 text-muted fs-5">
                                            <i class="fas fa-info-circle me-2 text-info"></i>
                                            <i>No ledger limits available.</i>
                                        </p>
                                        <form method="POST" action="#">
                                            @csrf
                                            <button type="submit" class="btn btn-success px-4 py-2">
                                                <i class="fas fa-sync-alt me-2"></i> Sync from Allocations
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        /* Custom hover effect for table rows */
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.2s ease;
        }

        /* Compact button styling */
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* Ensure table cells are compact */
        .table-sm th, .table-sm td {
            padding: 0.5rem;
        }

        /* Card shadow and border */
        .card {
            border: none;
            border-radius: 0.5rem;
        }

        /* Responsive table on small screens */
        @media (max-width: 576px) {
            .table-responsive {
                font-size: 0.875rem;
            }

            .btn-sm {
                padding: 0.2rem 0.4rem;
            }
        }
    </style>
@endsection
