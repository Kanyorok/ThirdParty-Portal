@extends('layouts.app')

@section('title', 'Reversing Journals')
@section('content')
    <div class="container my-0">
        <!-- Card for Reversing Journals -->
        <div class="card shadow-sm rounded-3" style="margin: 0.5rem;">
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-info"><i class="fas fa-undo-alt"></i>
                    Reversing Journals</h5>
                <a href="{{ route('reversingjournal.create') }}" class="btn btn-info btn-sm p-2">
                    <i class="fas fa-plus me-1"></i> New Reversal
                </a>
            </div>

            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle table-striped1 text-center">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Journal Ref</th>
                            <th>Original Ref</th>
                            <th>Reversal Date</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Reversed By</th>
                            <th class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($reversingJournals as $reversal)
                            @php
                                $reverse = $reversal->reverseJournals->first();
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                {{-- Journal Ref --}}
                                <td>{{ $reversal->RefNo ?? '-' }}</td>
                                {{-- Original Ref --}}
                                <td>{{ $reverse->OriginalReferenceNumber ?? '-' }}</td>

                                {{-- Reversal Date --}}
                                <td>{{ \Carbon\Carbon::parse($reverse->ReversalDate ?? $reversal->Date)->format('Y-m-d') }}</td>

                                {{-- Reason --}}
                                <td>{{ $reverse->Reason ?? '-' }}</td>

                                {{-- Status --}}
                                <td>
                                    @php
                                        $statusClass = match($reversal->ApprovalStatus) {
                                            'posted' => 'bg-success',
                                            'rejected' => 'bg-danger',
                                            'draft' => 'bg-secondary',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">
                                        {{ ucfirst($reversal->ApprovalStatus) ?? 'Pending' }}
                                    </span>
                                </td>

                                {{-- Reversed By --}}
                                <td>{{ $reversal->createdBy->Name ?? 'System' }}</td>

                                {{-- Actions --}}
                                <td class="text-center">
                                    <a href="{{ route('reversingjournal.show', $reversal->Id) }}"
                                       class="btn btn-sm btn-outline-info me-1" title="View Reversal">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('journalentry.show', $reverse->OriginalJournalEntryID) }}"
                                       class="btn btn-sm btn-outline-primary" title="View Journal Entry">
                                        <i class="fas fa-book-open"></i>
                                    </a>
                                    @php $isPosted = strtolower($reversal->ApprovalStatus ?? '') === 'posted'; @endphp
                                    <!-- Edit removed as requested -->
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger custom-delete-btn {{ $isPosted ? 'disabled' : '' }}"
                                            title="Delete"
                                            {{ $isPosted ? 'disabled' : '' }}
                                            @if(!$isPosted)
                                                data-bs-toggle="modal"
                                                data-bs-target="#customDeleteConfirmModal"
                                                data-name="{{ $reversal->RefNo ?? ('#'.$reversal->Id) }}"
                                                data-route="{{ route('reversingjournal.destroy', $reversal->Id) }}"
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
                                            <i>No reversing journal entries found.</i>
                                        </p>
                                        <a href="{{ route('reversingjournal.create') }}" class="btn btn-info px-2 py-2">
                                            <i class="fas fa-plus-circle me-1"></i> Add Reversing Journal
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
