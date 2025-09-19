@extends('layouts.app')

@section('title', 'Recurring Journals')

@section('content')
    <div class="container my-3">
        <!-- Card -->
        <div class="card shadow-sm rounded-3">
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-muted">
                    <i class="fas fa-sync-alt text-info me-2"></i> Recurring Journals data
                </h6>
                <a href="{{ route('recurrentjournal.create') }}" class="btn btn-info btn-sm p-2">
                    <i class="fas fa-plus-circle me-1"></i> New Recurring Journal
                </a>
            </div>

            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle table-striped1 text-center"
                           style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Reference No.</th>
                            <th>Description</th>
                            <th>Frequency</th>
                            <th>Start Date</th>
                            <th>Cutoff Date</th>
                            <th>Approval</th>
                            <th>Next Run</th>
                            <th class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($recurringJournals as $recurring)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>{{ $recurring->RefNo }}</td>

                                {{-- Reference Name --}}
                                <td>
                                    @if($recurring->recurringJournals && $recurring->recurringJournals->isNotEmpty())
                                        {{ $recurring->recurringJournals->first()->ReferenceName }}
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- Frequency --}}
                                <td>{{ $frequencies[$recurring->recurringJournals->first()->Frequency] ?? 'Unknown' }}</td>

                                {{-- Start Date --}}
                                <td>
                                    @if($recurring->recurringJournals && $recurring->recurringJournals->isNotEmpty())
                                        {{ optional($recurring->recurringJournals->first())->StartDate ? \Carbon\Carbon::parse($recurring->recurringJournals->first()->StartDate)->format('Y-m-d') : '-' }}
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- Cutoff Date --}}
                                <td>
                                    @if($recurring->recurringJournals && $recurring->recurringJournals->isNotEmpty())
                                        {{ optional($recurring->recurringJournals->first())->CuttOffDate ? \Carbon\Carbon::parse($recurring->recurringJournals->first()->CuttOffDate)->format('Y-m-d') : '-' }}
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- Approval Status --}}
                                <td>
                                    @php
                                        $statusClass = match($recurring->ApprovalStatus) {
                                            'posted' => 'bg-success',
                                            'rejected' => 'bg-danger',
                                            'draft' => 'bg-secondary',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">
                                        {{ ucfirst($recurring->ApprovalStatus) ?? 'Pending' }}
                                    </span>
                                </td>

                                {{-- Next Run --}}
                                @if($recurring->recurringJournals && $recurring->recurringJournals->isNotEmpty())
                                    <td>
                                        {{ optional($recurring->recurringJournals->first())->NextRunDate ? \Carbon\Carbon::parse($recurring->recurringJournals->first()->NextRunDate)->format('Y-m-d') : 'Not Set' }}
                                    </td>
                                @else
                                    <td class="text-center">
                                        Not Set
                                    </td>
                                @endif

                                {{-- Action Buttons --}}
                                <td class="text-center">
                                    <a href="{{ route('recurrentjournal.show', $recurring->Id) }}"
                                       class="btn btn-sm btn-outline-info me-1" title="View Journal Lines">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @php $isPosted = strtolower($recurring->ApprovalStatus ?? '') === 'posted'; @endphp
                                    <a href="{{ route('recurrentjournal.edit', $recurring->Id) }}"
                                       class="btn btn-sm btn-outline-warning me-1 {{ $isPosted ? 'disabled' : '' }}"
                                       title="Edit" aria-disabled="{{ $isPosted ? 'true' : 'false' }}"
                                       style="{{ $isPosted ? 'pointer-events:none; opacity:.65;' : '' }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger custom-delete-btn {{ $isPosted ? 'disabled' : '' }}"
                                            title="Delete"
                                            {{ $isPosted ? 'disabled' : '' }}
                                            @if(!$isPosted)
                                                data-bs-toggle="modal"
                                                data-bs-target="#customDeleteConfirmModal"
                                                data-name="{{ $recurring->RefNo ?? ('#'.$recurring->Id) }}"
                                                data-route="{{ route('recurrentjournal.destroy', $recurring->Id) }}"
                                            @endif
                                    >
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="text-center p-4 border rounded-3 bg-light">
                                        <p class="mb-3 text-muted fs-5">
                                            <i class="fas fa-info-circle me-2 text-info"></i>
                                            No recurring journals have been added yet.
                                        </p>
                                        <a href="{{ route('recurrentjournal.create') }}" class="btn btn-info px-4 py-2">
                                            <i class="fas fa-plus-circle me-2"></i> Add Recurring Journal
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

    @include('components.modals.delete-confirm')
@endsection

@section('styles')
    <style>
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.2s ease;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .table-sm th, .table-sm td {
            padding: 0.5rem;
        }

        .card {
            border: none;
            border-radius: 0.5rem;
        }

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
