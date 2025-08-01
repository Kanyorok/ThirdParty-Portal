@extends('layouts.app')
@section('title', 'Journal Entry')
@section('content')
    <div class="container my-3">
        <!-- Card for Journal Entries -->
        <div class="card shadow-sm rounded-3" style="margin: 0.5rem;">
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
{{--                <h5 class="mb-0 text-primary">📒 Journal Entries</h5>--}}
                <a href="{{ route('journalentry.create') }}" class="btn btn-primary btn-sm p-2">
                    <i class="fas fa-plus me-1"></i> New Journal Entry
                </a>
            </div>
            <div class="card-body p-3">
                <!-- Journal Entries Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle">
                        <thead class="table-light">
                        <tr>
                            <th scope="col">Reference</th>
                            <th scope="col">Date</th>
                            <th scope="col">Description</th>
                            <th scope="col">Total Debit</th>
                            <th scope="col">Total Credit</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>JV20240601</td>
                            <td>2025-06-01</td>
                            <td>Salary Payment - May</td>
                            <td>100,000.00</td>
                            <td>100,000.00</td>
                            <td><span class="badge bg-success">Posted</span></td>
                            <td class="text-center">
                                <a href="#" class="btn btn-sm btn-outline-info me-1" title="View Journal Entry">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
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
