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
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $r->budget->Name ?? '—' }}</td>
                                {{--                                <td>{{ $r->fromLine->LineName ?? '—Null Line-' }}</td>--}}
                                <td>
                                    <div>{{ $r->fromLine->LineName ?? '—Null Line-' }}</div>
                                    <small
                                        class="text-muted">{{ $r->fromLine->department->Name.' Dept' ?? '—No Department—' }}</small>
                                </td>
                                <td>
                                    <div>{{ $r->toLine->LineName ?? '—Null Line-' }}</div>
                                    <small class="text-muted">{{ $r->toLine->department?->Name.' Dept' ?? '—No Department—' }}</small>

                                </td>

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
                                         <a href="{{ route('budgetandanalytics.reallocation.show', ['id' => $r->id]) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="View">
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


    <!-- Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reviewModalLabel">
                        <i class="fas fa-random me-2"></i>Budget Reallocation Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading reallocation details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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



@section('scripts')
    <script>
        function loadReallocationDetails(id) {
            // Reset modal content to loading state
            document.getElementById('modalContent').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading reallocation details...</p>
        </div>
    `;

            // Make AJAX request
            fetch(`/budgetandanalytics/reallocation/${id}/details`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalContent').innerHTML = `
                <div class="row mb-4">
                    <div class="col-md-4">
                        <h6 class="text-primary mb-3">Basic Information</h6>
                        <div class="card border-0 bg-light">
                            <div class="card-body p-3">
                                <div class="row g-2">
                                    <div class="col-12"><strong>Budget:</strong> ${data.budget_name || '—'}</div>
                                    <div class="col-12"><strong>Branch:</strong> ${data.branch_name || '—'}</div>
                                    <div class="col-12"><strong>Department:</strong> ${data.department_name || '—'}</div>
                                    <div class="col-12"><strong>Type:</strong> ${data.reallocation_type || '—'}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-primary mb-3">Amount & Status</h6>
                        <div class="card border-0 bg-light">
                            <div class="card-body p-3 text-center">
                                <h4 class="text-success mb-2">${data.amount}</h4>
                                <span class="badge ${data.status_class} fs-6">${data.status}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-primary mb-3">Dates</h6>
                        <div class="card border-0 bg-light">
                            <div class="card-body p-3">
                                <div class="row g-2">
                                    <div class="col-12"><strong>Created:</strong> ${data.created_on || '—'}</div>
                                    <div class="col-12"><strong>Approved:</strong> ${data.approved_on || 'Pending'}</div>
                                    <div class="col-12"><strong>By:</strong> ${data.approved_by || '—'}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Line Selections & Allocations -->
                <div class="row g-3 mb-4">
                    <!-- FROM side -->
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-arrow-left me-2"></i>From Budget Line</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>Line:</strong> ${data.from_line}<br>
                                    <small class="text-muted">Department: ${data.from_dept || '—'}</small>
                                </div>

                                <div class="mb-3">
                                    <span class="badge bg-light text-dark border me-2">Allocated: <strong>${data.from_summary?.allocated || '0.00'}</strong></span>
                                    <span class="badge bg-light text-dark border me-2">Usage: <strong>${data.from_summary?.usage || '0.00'}</strong></span>
                                    <span class="badge ${(data.from_summary?.balance || 0) >= 0 ? 'bg-success' : 'bg-danger'}">Balance: <strong>${data.from_summary?.balance || '0.00'}</strong></span>
                                </div>

                                ${data.from_allocations && data.from_allocations.length > 0 ? `
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="table-light">
                                            <tr><th>Month</th><th class="text-end">Allocation</th></tr>
                                        </thead>
                                        <tbody>
                                            ${data.from_allocations.map(alloc => `
                                                <tr>
                                                    <td>${alloc.month}</td>
                                                    <td class="text-end">${alloc.amount}</td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                                ` : '<p class="text-muted">No allocation data</p>'}
                            </div>
                        </div>
                    </div>

                    <!-- TO side -->
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-arrow-right me-2"></i>To Budget Line</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>Line:</strong> ${data.to_line}<br>
                                    <small class="text-muted">Department: ${data.to_dept || '—'}</small>
                                </div>

                                <div class="mb-3">
                                    <span class="badge bg-light text-dark border me-2">Allocated: <strong>${data.to_summary?.allocated || '0.00'}</strong></span>
                                    <span class="badge bg-light text-dark border me-2">Usage: <strong>${data.to_summary?.usage || '0.00'}</strong></span>
                                    <span class="badge ${(data.to_summary?.balance || 0) >= 0 ? 'bg-success' : 'bg-danger'}">Balance: <strong>${data.to_summary?.balance || '0.00'}</strong></span>
                                </div>

                                ${data.to_allocations && data.to_allocations.length > 0 ? `
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="table-light">
                                            <tr><th>Month</th><th class="text-end">Allocation</th></tr>
                                        </thead>
                                        <tbody>
                                            ${data.to_allocations.map(alloc => `
                                                <tr>
                                                    <td>${alloc.month}</td>
                                                    <td class="text-end">${alloc.amount}</td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                                ` : '<p class="text-muted">No allocation data</p>'}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Justification -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 text-primary"><i class="fas fa-comment-alt me-2"></i>Justification</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">${data.justification || 'No justification provided'}</p>
                    </div>
                </div>

                ${data.budget_limits && data.budget_limits.length > 0 ? `
                <div class="card mt-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 text-primary"><i class="fas fa-chart-line me-2"></i>Budget Limits</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Ledger ID</th>
                                        <th>Limit Type</th>
                                        <th>Amount</th>
                                        <th>Effective Period</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.budget_limits.map(limit => `
                                        <tr>
                                            <td>${limit.ledger_id}</td>
                                            <td>${limit.limit_type}</td>
                                            <td class="text-end">${limit.limit_amount}</td>
                                            <td>${limit.effective_from} - ${limit.effective_to || 'Ongoing'}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                ` : ''}
            `;
                })
                .catch(error => {
                    document.getElementById('modalContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error loading reallocation details. Please try again.
                </div>
            `;
                    console.error('Error:', error);
                });
        }
    </script>
@endsection
