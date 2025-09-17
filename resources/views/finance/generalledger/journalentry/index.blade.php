@extends('layouts.app')
@section('title', 'Journal Entry')
@section('content')
    <div class="container my-3">
        <!-- Card for Journal Entries -->
        <div class="card shadow-sm rounded-3" style="margin: 0.5rem;">
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary">📒 Journal Entries</h5>
                <a href="{{ route('journalentry.create') }}" class="btn btn-info btn-sm p-2">
                    <i class="fas fa-plus me-1"></i> New Journal Entry
                </a>
            </div>
            <div class="card-body p-3">
                <!-- Journal Entries Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle table-striped1"
                           style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;"
                    >
                        <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Reference</th>
                            <th scope="col">Date</th>
                            <th scope="col">Description</th>
                            <th scope="col">Total Debit</th>
                            <th scope="col">Total Credit</th>
                            <th scope="col">Approval</th>
                            <th scope="col" class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($journalEntries as $journalEntry)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $journalEntry->RefNo ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($journalEntry->JournalDate)->format('Y-m-d') }}</td>
                                <td>{{ $journalEntry->Description ?? '-' }}</td>
                                <td>
                                    {{ number_format($journalEntry->journalLines->sum(function($line) {
                                        return floatval($line->Debit);
                                    }), 2) }}
                                </td>
                                <td>
                                    {{ number_format($journalEntry->journalLines->sum(function($line) {
                                        return floatval($line->Credit);
                                    }), 2) }}
                                </td>
                                <td>
                                    @php
                                        $statusClass = match($journalEntry->ApprovalStatus) {
                                            'posted' => 'bg-success',
                                            'rejected' => 'bg-danger',
                                            'draft' => 'bg-secondary',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">
                                        {{ ucfirst($journalEntry->ApprovalStatus) ?? 'Pending' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('journalentry.show', $journalEntry->Id) }}" class="btn btn-sm btn-outline-info me-1" title="View Journal Entry">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @php $isPosted = strtolower($journalEntry->ApprovalStatus ?? '') === 'posted'; @endphp
                                    <a href="{{ $isPosted ? '#' : route('journalentry.edit', $journalEntry->Id) }}"
                                       class="btn btn-sm btn-outline-primary me-1 {{ $isPosted ? 'disabled' : '' }}"
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
                                                data-name="{{ $journalEntry->RefNo ?? ('#'.$journalEntry->Id) }}"
                                                data-route="{{ route('journalentry.destroy', $journalEntry->Id) }}"
                                            @endif
                                    >
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-0">
                                    <div class="text-center p-4 border rounded-3 bg-light">
                                        <p class="mb-3 text-muted fs-5">
                                            <i class="fas fa-info-circle me-2 text-info"></i>
                                            <i>No journal entries have been added yet.</i>
                                        </p>
                                        <a href="{{ route('journalentry.create') }}" class="btn btn-info px-4 py-2">
                                            <i class="fas fa-plus-circle me-2"></i> Add Journal Entry
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
