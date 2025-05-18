@extends('layouts.app')

@section('title', 'Tenders Management')

@section('content')
<div class="container py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-gavel text-primary me-2"></i>Tenders List
        </h1>
        <a href="{{ route('initiatetender.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>New Tender
        </a>
    </div>

    <!-- Status Messages -->
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Filters Section -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
        </div>
        <div class="card-body">
            <form id="tender-filters" method="GET" action="{{ route('initiatetender.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">All Statuses</option>
                            @foreach(\App\Enums\TenderStatusEnum::cases() as $status)
                            <option value="{{ $status->value }}" @selected(request('status')==$status->value)>
                                {{ $status->displayName() }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Type</label>
                        <select class="form-select" name="type">
                            <option value="">All Types</option>
                            @foreach(\App\Enums\TenderTypeEnum::cases() as $type)
                            <option value="{{ $type->value }}" @selected(request('type')==$type->value)>
                                {{ $type->displayName() }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category">
                            <option value="">All Categories</option>
                            @foreach(\App\Enums\TenderCategoryEnum::cases() as $category)
                            <option value="{{ $category->value }}" @selected(request('category')==$category->value)>
                                {{ $category->displayName() }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> Apply
                            </button>
                            <a href="{{ route('initiatetender.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-undo me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tenders Table -->
    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Tenders</h5>
            <div class="text-muted small">
                {{ $tenders->total() }} records found
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th>Tender Title</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tenders as $tender)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('initiatetender.show', $tender->Id) }}" class="text-dark">
                                    {{ Str::limit($tender->Title, 40) }}
                                </a>
                                @if($tender->Status === \App\Enums\TenderStatusEnum::Draft)
                                <span class="badge bg-info ms-2">Draft</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary">
                                    {{ $tender->TenderType->displayName() }}
                                </span>
                            </td>
                            <td>{{ $tender->TenderCategory->displayName() }}</td>
                            <td>
                                <span class="badge bg-{{ $tender->Status->colorClass() }}">
                                    {{ $tender->Status->displayName() }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a href="{{ route('initiatetender.show', $tender->Id) }}"
                                        class="btn btn-sm btn-outline-primary"
                                        title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($tender->Status === \App\Enums\TenderStatusEnum::Draft)
                                    <a href="{{ route('initiatetender.edit', $tender->Id) }}"
                                        class="btn btn-sm btn-outline-success"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('initiatetender.destroy', $tender->Id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Delete this tender?')"
                                            title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                    <h5>No Tenders Found</h5>
                                    <p class="text-muted">
                                        @if(request()->except('page'))
                                        No matching tenders. Try adjusting filters.
                                        @else
                                        No tenders initiated yet.
                                        @endif
                                    </p>
                                    <a href="{{ route('initiatetender.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Create Tender
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($tenders->hasPages())
        <div class="card-footer">
            {{ $tenders->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .empty-state {
        max-width: 400px;
        margin: 0 auto;
    }

    .table th {
        white-space: nowrap;
    }

    @media (max-width: 768px) {
        .table-responsive {
            overflow-x: auto;
        }
    }
</style>
@endpush