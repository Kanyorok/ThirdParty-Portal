@extends('layouts.app')
@section('title', 'Legal Templates')

@section('styles')
    <style>
        .badge-pill { border-radius: 999px; }
        .table thead th { white-space: nowrap; }
    </style>
@endsection

@section('content')
    <div class="container my-3">
        <div class="card shadow rounded-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-info"><i class="fa-solid fa-file-contract me-2"></i> Legal Templates</h5>
                <a href="{{ route('legal.templates.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus me-1"></i> New Template
                </a>
            </div>

            <div class="card-body">
                <form class="row g-3 align-items-end mb-3" method="GET" action="{{ route('legal.templates.index') }}">
                    <div class="col-md-5">
                        <label class="form-label">Search</label>
                        <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Title, type, version, description">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Document Type</label>
                        <select name="type" class="form-select">
                            <option value="">All</option>
                            @foreach($types as $t)
                                <option value="{{ $t }}" @selected($type===$t)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Any</option>
                            @foreach($statuses as $st)
                                <option value="{{ $st }}" @selected($status===$st)>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary flex-fill" type="submit">
                                <i class="fa-solid fa-magnifying-glass me-1"></i> Search
                            </button>
{{--                            <a href="{{ route('legal.templates.index') }}" class="btn btn-outline-secondary flex-fill">--}}
{{--                                <i class="fas fa-refresh me-1"></i> Reset--}}
{{--                            </a>--}}
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle text-center">
                        <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Version</th>
{{--                            <th>Status</th>--}}
                            <th>Active</th>
                            <th>Clauses</th>
                            <th>Created</th>
                            <th>DMS</th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($templates as $t)
                            <tr>
                                <td>
                                    <a href="{{ route('legal.templates.show', $t->Id) }}" class="fw-semibold text-decoration-none">
                                        {{ $t->Title }}
                                    </a>
                                    @if($t->Description)
                                        <div class="small text-muted">{{ \Illuminate\Support\Str::limit($t->Description, 80) }}</div>
                                    @endif
                                </td>
                                <td>{{ $t->DocumentType ?? '—' }}</td>
                                <td>{{ $t->Version ?? '—' }}</td>
{{--                                <td>--}}
{{--                                    @php--}}
{{--                                        $color = match($t->Status){--}}
{{--                                          'ACTIVE' => 'success',--}}
{{--                                          'DRAFT' => 'secondary',--}}
{{--                                          'DEPRECATED' => 'warning',--}}
{{--                                          'ARCHIVED' => 'dark',--}}
{{--                                          default => 'secondary'--}}
{{--                                        };--}}
{{--                                    @endphp--}}
{{--                                    <span class="badge text-bg-{{ $color }} badge-pill">{{ $t->Status }}</span>--}}
{{--                                </td>--}}
                                <td>
                                    @if($t->IsActive)
                                        <span class="badge text-bg-success">Yes</span>
                                    @else
                                        <span class="badge text-bg-secondary">No</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge text-bg-info">{{ $t->clauses_count }}</span>
                                </td>
                                <td>
                                    <div class="small">{{ optional($t->CreatedOn)->format('Y-m-d H:i') ?? \Carbon\Carbon::parse($t->CreatedOn)->format('Y-m-d H:i') }}</div>
                                </td>
                                <td>
                                    @if($t->DocumentDMSID)
                                        <span class="badge text-bg-primary" title="Archived in DMS">#{{ $t->DocumentDMSID }}</span>
                                    @else
                                        <span class="badge text-bg-light text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('legal.templates.show', $t->Id) }}" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="fa-regular fa-eye me-1"></i> View
                                    </a>
                                    {{-- Add Edit/Delete if you implement them later --}}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">No templates yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $templates->links() }}
            </div>
        </div>
    </div>
@endsection

