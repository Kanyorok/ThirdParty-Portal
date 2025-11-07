@extends('layouts.app')
@section('title', 'Budget Activities')
@section('content')
    <div class="container my-3">
        <div class="card shadow-sm rounded-3 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-1 text-primary">📊 Budget: {{ $budget->Name }}</h5>
                    <p class="text-muted mb-0">Period: {{ $budget->From }} – {{ $budget->To }}</p>
                </div>
                <a href="{{ route('budgetactivities.index') }}" class="btn btn-secondary btn-sm px-3 py-2">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle"
                       style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 13px;">
                    <thead class="table-light">
                    <tr class="text-start">
                        <th>#</th>
                        <th>Activity Name</th>
                        {{--                        <th>Description</th>--}}
                        <th>Budget Line</th>
                        <th>Branch</th>
                        {{--                        <th>Allocation Type</th>--}}
                        <th>Totals</th>
                        <th>Allocations</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php $total = 0; @endphp
                    @foreach ($activities as $i => $activity)
                        @php $total += $activity->FullAllocation; @endphp
                        <tr class="text-start">
                            <td>{{ $i + 1 }}</td>
                            <td class="text-truncate" style="max-width: 200px;"
                                title="{{ $activity->activity->ActivityName }}">
                                {{ $activity->activity->ActivityName }}
                            </td>
                            {{--                            <td class="text-truncate" style="max-width: 200px;" title="{{ $activity->Description }}">--}}
                            {{--                                {{ $activity->Description }}--}}
                            {{--                            </td>--}}
                            <td>{{ $activity->budgetLine->LineName ?? '-' }}</td>
                            <td>{{ $activity->branch->Name ?? '-' }}</td>
                            {{--                            <td>{{ ucfirst($activity->AllocationType) }}</td>--}}
                            <td>{{ number_format($activity->FullAllocation, 2) }}</td>
                            <td>
                                @if($activity->AllocationType === 'monthly' && $activity->allocations && count($activity->allocations))
                                    <button class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#monthlyAllocModal-{{ $activity->Id }}">
                                        View
                                    </button>
                                    {{-- Monthly Allocations Modal --}}
                                    <div class="modal fade" id="monthlyAllocModal-{{ $activity->Id }}" tabindex="-1"
                                         aria-labelledby="monthlyAllocModalLabel-{{ $activity->Id }}"
                                         aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-3 shadow">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"
                                                        id="monthlyAllocModalLabel-{{ $activity->Id }}">
                                                        📆 Monthly Allocations
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <table class="table table-sm table-bordered">
                                                        <thead class="table-light">
                                                        <tr>
                                                            <th>Month</th>
                                                            <th>Amount</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @foreach($activity->allocations as $alloc)
                                                            <tr>
                                                                <td>Month {{ $alloc->Month }}</td>
                                                                <td>{{ number_format($alloc->Amount, 2) }}</td>
                                                            </tr>
                                                        @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($budget->Status==='approved')
                                    <span class="badge rounded-pill bg-success text-white text-decoration-none">
                                               Approved budget
                                        </span>
                                @else
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('budgetactivities.edit', $activity->Id) }}"
                                           class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger custom-delete-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#customDeleteConfirmModal"
                                                data-name="{{ $activity->activity->ActivityName }}"
                                                data-route="{{ route('budgetactivities.destroy', $activity->Id) }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                @endif

                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot class="table-light">
                    <tr>
                        <th colspan="6" class="text-end">Total Allocation</th>
                        <th colspan="3">{{ number_format($total, 2) }}</th>
                    </tr>
                    </tfoot>
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

        .text-truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
