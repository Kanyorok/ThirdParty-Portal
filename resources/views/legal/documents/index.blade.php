@extends('layouts.app')
@section('title', 'Legal Documents')
@section('content')
    <div class="container">
        <div class="card p-2 shadow rounded-4 border-0">
            <div class="card-header bg-light px-3 py-2 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-info">
                    <i class="fas fa-folder me-2"></i> Legal Documents Registry
                </h5>
                <a href="{{ route('legal.documents.create') }}" class="btn btn-info btn-sm p-2">
                    <i class="fas fa-plus me-1"></i> Add New Document
                </a>
            </div>

            <div class="card-body">

                {{-- Filters --}}
{{--                <form method="GET" class="row g-2 mb-3">--}}
{{--                    <div class="col-md-3">--}}
{{--                        <input type="text" name="q" class="form-control" placeholder="Search title/type/source..."--}}
{{--                               value="{{ $filters['q'] ?? '' }}">--}}
{{--                    </div>--}}
{{--                    <div class="col-md-2">--}}
{{--                        <select name="type" class="form-select">--}}
{{--                            <option value="">All Types</option>--}}
{{--                            @foreach (['Contract','Lease','NDA','MOU'] as $t)--}}
{{--                                <option value="{{ $t }}" @selected(($filters['type'] ?? '') === $t)>{{ $t }}</option>--}}
{{--                            @endforeach--}}
{{--                        </select>--}}
{{--                    </div>--}}
{{--                    <div class="col-md-2">--}}
{{--                        <select name="source" class="form-select">--}}
{{--                            <option value="">All Sources</option>--}}
{{--                            @foreach (['Legal','Procurement','Property','HR','Insurance'] as $s)--}}
{{--                                <option value="{{ $s }}" @selected(($filters['source'] ?? '') === $s)>{{ $s }}</option>--}}
{{--                            @endforeach--}}
{{--                        </select>--}}
{{--                    </div>--}}
{{--                    <div class="col-md-2">--}}
{{--                        <select name="review" class="form-select">--}}
{{--                            <option value="">All Review</option>--}}
{{--                            @foreach (['Draft','In Review','Approved','Rejected'] as $r)--}}
{{--                                <option value="{{ $r }}" @selected(($filters['review'] ?? '') === $r)>{{ $r }}</option>--}}
{{--                            @endforeach--}}
{{--                        </select>--}}
{{--                    </div>--}}
{{--                    <div class="col-md-2">--}}
{{--                        <select name="exec" class="form-select">--}}
{{--                            <option value="">All Execution</option>--}}
{{--                            @foreach (['Pending','Signed','Archived'] as $e)--}}
{{--                                <option value="{{ $e }}" @selected(($filters['exec'] ?? '') === $e)>{{ $e }}</option>--}}
{{--                            @endforeach--}}
{{--                        </select>--}}
{{--                    </div>--}}

{{--                    <div class="w-100 d-none d-md-block"></div>--}}

{{--                    <div class="col-md-2">--}}
{{--                        <input type="date" name="from" class="form-control" value="{{ $filters['from'] ?? '' }}">--}}
{{--                        <small class="text-muted">From</small>--}}
{{--                    </div>--}}
{{--                    <div class="col-md-2">--}}
{{--                        <input type="date" name="to" class="form-control" value="{{ $filters['to'] ?? '' }}">--}}
{{--                        <small class="text-muted">To</small>--}}
{{--                    </div>--}}
{{--                    <div class="col-md-3 d-flex gap-2">--}}
{{--                        <button type="submit" class="btn btn-primary flex-grow-1">Search</button>--}}
{{--                        <a href="{{ route('legal.documents.index') }}" class="btn btn-outline-secondary">Reset</a>--}}
{{--                    </div>--}}
{{--                </form>--}}

                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Source</th>
{{--                            <th>Review</th>--}}
                            <th>Execution</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($documents as $doc)
                            <tr>
                                <td class="fw-semibold">{{ $doc->DocumentTitle }}</td>
                                <td>{{ $doc->DocumentType }}</td>
                                <td>{{ $doc->SourceModule }}</td>
{{--                                <td>--}}
{{--                                    @php--}}
{{--                                        $review = $doc->ReviewStatus;--}}
{{--                                        $reviewClass = match($review) {--}}
{{--                                            'Approved' => 'success',--}}
{{--                                            'In Review' => 'warning',--}}
{{--                                            'Rejected' => 'danger',--}}
{{--                                            default => 'secondary'--}}
{{--                                        };--}}
{{--                                    @endphp--}}
{{--                                    <span class="badge bg-{{ $reviewClass }}">{{ $review ?: '—' }}</span>--}}
{{--                                </td>--}}
                                <td>
                                    @php
                                        $exec = $doc->ExecutionStatus;
                                        $execClass = match($exec) {
                                            'Signed' => 'success',
                                            'Archived' => 'dark',
                                            'Pending' => 'warning',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $execClass }}">{{ $exec ?: '—' }}</span>
                                </td>
                                <td>
                                    @if($doc->CreatedOn)
                                        {{ \Carbon\Carbon::parse($doc->CreatedOn)->format('d M Y H:i') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    {{-- Always allow view --}}
                                    <a href="{{ route('legal.documents.show', $doc->Id) }}"
                                       class="btn btn-sm btn-outline-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @if($doc->ExecutionStatus === 'Pending')
                                        {{-- Only show edit/delete if NOT pending --}}
                                        <a href="{{ route('legal.documents.edit', $doc->Id) }}"
                                           class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-danger custom-delete-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#customDeleteConfirmModal"
                                                data-name="{{ $doc->DocumentTitle }}"
                                                data-route="{{ route('legal.documents.destroy', $doc->Id) }}"
                                                title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    @else
                                        {{-- Disabled buttons for Pending --}}
                                        <button class="btn btn-sm btn-outline-warning" disabled>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" disabled>
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    @endif
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No documents found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $documents->links() }}
            </div>
        </div>
    </div>


    @include('components.modals.delete-confirm')
@endsection
