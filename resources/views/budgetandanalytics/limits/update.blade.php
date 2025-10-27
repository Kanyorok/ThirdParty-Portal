@extends('layouts.app')
@section('title', 'Update Ledger Limits')

@section('content')
    <div class="container my-3">
        <!-- Card -->
        <div class="card shadow-sm rounded-3" style="margin: 0.5rem;">
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-info">⚙️</h5>
                <small class="text-muted">Update Ledger Limits From Budget Allocations</small>
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

                <!-- Update Form -->
                <form method="POST" action="{{ route('budgetandanalytics.limits.runUpdate') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="BudgetID" class="form-label fw-semibold">Select Budget</label>
                        <select id="BudgetID" name="BudgetID" class="form-select shadow-sm" required>
                            <option value="">-- Choose Active Budget --</option>
                            @foreach($budgets as $budget)
                                <option value="{{ $budget->Id }}">
                                    {{ $budget->Name }} (FY {{ $budget->FiscalYear }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-outline-danger px-4 py-2">
                            <i class="fas fa-rocket me-2"></i>Run Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        /* Card styling */
        .card {
            border: none;
            border-radius: 0.5rem;
        }

        /* Form select styling */
        .form-select {
            border-radius: 0.35rem;
            border: 1px solid #ced4da;
        }

        /* Button styling */
        .btn-outline-info {
            color: #0dcaf0;
            border-color: #0dcaf0;
            transition: all 0.2s ease-in-out;
        }

        .btn-outline-info:hover {
            background-color: #0dcaf0;
            color: #fff;
        }

        /* Label styling */
        .form-label {
            font-weight: 600;
            color: #495057;
        }

        /* Container padding for mobile */
        @media (max-width: 576px) {
            .container {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
        }
    </style>
@endsection
