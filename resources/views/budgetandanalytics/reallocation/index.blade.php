@extends('layouts.app')
@section('title', 'Budget Reallocations')

@section('content')
    <div class="container my-3">
        <div class="card shadow-sm rounded-3">
            <!-- Header -->
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary">
                    <i class="fas fa-random me-2"></i>
                </h5>
                <a href="{{ route('budgetandanalytics.reallocation.create') }}" class="btn btn-info btn-sm">
                    <i class="fas fa-plus me-1"></i> New Reallocation
                </a>
            </div>

            <!-- Body -->
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle table-striped" style="font-size: 13px">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Budget</th>
                            <th>Branch</th>
                            <th>Department</th>
                            <th>From Line</th>
                            <th>To Line</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($reallocations as $r)
                            <tr>
                                <td>{{ $r->id }}</td>
                                <td>{{ $r->budget->Name ?? '—' }}</td>
                                <td>{{ $r->branch->Name ?? '—' }}</td>
                                <td>{{ $r->department->Name ?? '—' }}</td>
                                <td>{{ $r->fromLine->LineName ?? '—' }}</td>
                                <td>{{ $r->toLine->LineName ?? '—' }}</td>
                                <td>{{ number_format($r->Amount, 2) }}</td>
                                <td>
                                    @php
                                        $badgeClass = match(strtolower($r->Status)) {
                                            'approved' => 'bg-success',
                                            'pending'  => 'bg-warning text-dark',
                                            'rejected' => 'bg-danger',
                                            default    => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">
                                        {{ ucfirst($r->Status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('budgetandanalytics.reallocation.review',$r->id) }}"
                                       class="btn btn-sm btn-outline-primary" title="Review">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-0">
                                    <div class="text-center p-4 border rounded-3 bg-light">
                                        <p class="mb-2 text-muted">
                                            <i class="fas fa-info-circle me-2 text-info"></i>
                                            No reallocation requests found
                                        </p>
                                        <a href="{{ route('budgetandanalytics.reallocation.create') }}"
                                           class="btn btn-info btn-sm">
                                            <i class="fas fa-plus-circle me-1"></i> Add Reallocation
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
    </div>
@endsection

@section('styles')
    <style>
        .card {
            border: none;
            border-radius: 0.5rem;
        }
        .btn {
            font-size: 0.85rem;
        }
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.2s ease;
        }
    </style>
@endsection
