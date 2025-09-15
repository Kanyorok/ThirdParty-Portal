@extends('layouts.app')
@section('title', 'Budget Lines Entries')

@section('content')
    <div class="container mt-2">
        <div class="card shadow-sm rounded-3">
            <!-- Header -->
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2 px-3">
                <h6 class="mb-0 text-primary">
                    <i class="fas fa-project-diagram me-2"></i> Budget Lines, Branches & Amounts
                </h6>
                <a href="{{ route('entrybyglline.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Entries
                </a>
            </div>

            <!-- Body -->
            <div class="card-body p-3">
                <p class="text-muted mb-3" style="font-size: 14px;">
                    Entries grouped by budget line and branch with amounts and allocations.
                </p>

                @if($entries->count())
                    @php
                        $totalAllocation = $entries->flatMap(fn($entry) => $entry->allocations ?? collect())->sum('Allocation');
                    @endphp

                        <!-- Summary -->
                    <div class="mb-3">
                        <p class="mb-1"><strong>Budget:</strong> {{ $entries->first()->budget->Name ?? '' }}</p>
                        <p class="mb-1"><strong>Total Allocations:</strong> {{ number_format($totalAllocation, 2) }}</p>
                        <p class="mb-1"><strong>Source:</strong> <span class="badge bg-secondary">Manual Entry</span></p>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped align-middle text-center budget-lines-table" style="min-width: 700px;">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Branch</th>
                                <th>Budget Line</th>
                                <th>Amount</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($entries as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->branch->Name }}</td>
                                    <td>{{ $item->budgetline->LineName }}</td>
                                    <td>{{ number_format($item->Amount, 2) }}</td>
                                    <td class="text-center" style="white-space: nowrap;">
                                        <!-- View Allocations -->
                                        <button class="btn btn-sm btn-outline-info me-1" data-bs-toggle="modal"
                                                data-bs-target="#allocModal{{ $item->Id }}" title="View Allocations">
                                            <i class="fas fa-list-alt"></i>
                                        </button>
                                        <!-- Edit -->
                                        <a href="{{ route('entrybyglline.edit', $item->Id) }}"
                                           class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <!-- Delete -->
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger custom-delete-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#customDeleteConfirmModal"
                                                data-name="{{ $item->budgetline->LineName }}"
                                                data-route="{{ route('entrybyglline.destroy', $item->Id) }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>

                                        <!-- Allocation Modal -->
                                        <div class="modal fade" id="allocModal{{ $item->Id }}" tabindex="-1" aria-labelledby="allocModalLabel{{ $item->Id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h6 class="modal-title" id="allocModalLabel{{ $item->Id }}">
                                                            Monthly Allocations – {{ $item->budgetline->LineName }}
                                                        </h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        @if($item->allocations && $item->allocations->count())
                                                            <table class="table table-bordered table-sm text-center">
                                                                <thead class="table-light">
                                                                <tr>
                                                                    <th>Month</th>
                                                                    <th>Allocation</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                @foreach($item->allocations as $alloc)
                                                                    <tr>
                                                                        <td>{{ $alloc->Month }}</td>
                                                                        <td>{{ number_format($alloc->Allocation, 2) }}</td>
                                                                    </tr>
                                                                @endforeach
                                                                </tbody>
                                                            </table>
                                                        @else
                                                            <div class="alert alert-info mb-0">No allocations found for this entry.</div>
                                                        @endif
                                                        <div class="mt-2">
                                                            <p class="text-muted mb-0">Total Allocated:
                                                                <strong>{{ number_format($item->Amount, 2) }}</strong>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End Modal -->
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center p-4 border rounded-3 bg-light">
                        <p class="mb-2 text-muted">
                            <i class="fas fa-info-circle me-2 text-info"></i>
                            No budget lines found.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @include('components.modals.delete-confirm')
@endsection

@section('styles')
    <style>
        .budget-lines-table {
            font-size: 13px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .budget-lines-table th,
        .budget-lines-table td {
            vertical-align: middle;
            text-align: center;
        }
    </style>
@endsection
