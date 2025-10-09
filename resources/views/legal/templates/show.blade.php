@extends('layouts.app')
@section('title', 'Template: '.$template->Title)

@section('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        .badge-pill { border-radius: 999px; }
        .clause-card { border:1px solid #e5e7eb; border-radius:.75rem; padding:.75rem; }
    </style>
@endsection

@section('content')
    <div class="container my-3">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h4 class="mb-1">{{ $template->Title }}</h4>
                <div class="d-flex flex-wrap gap-2">
                    @php
                        $color = match($template->Status){
                          'ACTIVE' => 'success',
                          'DRAFT' => 'secondary',
                          'DEPRECATED' => 'warning',
                          'ARCHIVED' => 'dark',
                          default => 'secondary'
                        };
                    @endphp
                    <span class="badge text-bg-{{ $color }} badge-pill">{{ $template->Status }}</span>
                    @if($template->IsActive)<span class="badge text-bg-success">Active</span>@else<span class="badge text-bg-secondary">Inactive</span>@endif
                    <span class="badge text-bg-info">v{{ $template->Version ?? '—' }}</span>
                    @if($template->DocumentType)<span class="badge text-bg-light border">Type: {{ $template->DocumentType }}</span>@endif
                    @if($template->DocumentDMSID)<span class="badge text-bg-primary" title="DMS Document ID">DMS #{{ $template->DocumentDMSID }}</span>@endif
                </div>
                @if($template->Description)
                    <div class="text-muted mt-2">{{ $template->Description }}</div>
                @endif
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('legal.templates.index') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back
                </a>
                {{-- If you add an edit route later, surface it here --}}
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card shadow-sm rounded-4">
                    <div class="card-header bg-light py-2 px-3">
                        <h6 class="mb-0 text-info"><i class="fa-regular fa-file-lines me-1"></i> Template Body</h6>
                    </div>
                    <div class="card-body">
                        {{-- Render CKEditor HTML safely --}}
                        <div class="ck-content">{!! $template->TemplateBody !!}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm rounded-4 mb-3">
                    <div class="card-header bg-light py-2 px-3">
                        <h6 class="mb-0 text-info"><i class="fa-solid fa-thumbtack me-1"></i> Attached Clauses ({{ $clauses->count() }})</h6>
                    </div>
                    <div class="card-body">
                        @forelse($clauses as $i => $c)
                            <div class="clause-card mb-2">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="fw-semibold">{{ ($i+1).'. '.$c->Title }}</div>
                                        <div class="small text-muted">{{ $c->ClauseType ?? '—' }} • v{{ $c->Version ?? '—' }}</div>
                                    </div>
                                    @if($c->pivot?->IsMandatory)
                                        <span class="badge text-bg-warning">Mandatory</span>
                                    @endif
                                </div>
                                <div class="small mt-2 text-muted">
                                    {{ \Illuminate\Support\Str::limit($c->Content, 160) }}
                                </div>
                            </div>
                        @empty
                            <div class="text-muted">No clauses attached.</div>
                        @endforelse
                    </div>
                </div>

                <div class="card shadow-sm rounded-4">
                    <div class="card-header bg-light py-2 px-3">
                        <h6 class="mb-0 text-info"><i class="fa-regular fa-circle-question me-1"></i> Details</h6>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-5">Jurisdiction</dt><dd class="col-7">{{ $template->Jurisdiction ?? '—' }}</dd>
                            <dt class="col-5">Effective From</dt><dd class="col-7">{{ $template->EffectiveFrom ?? '—' }}</dd>
                            <dt class="col-5">Effective To</dt><dd class="col-7">{{ $template->EffectiveTo ?? '—' }}</dd>
                            <dt class="col-5">Approval</dt>
                            <dd class="col-7">
                                {{ $template->ApprovalStatus ?? '—' }}
                                @if($template->ApprovedOn)
                                    <span class="text-muted">on {{ \Carbon\Carbon::parse($template->ApprovedOn)->format('Y-m-d H:i') }}</span>
                                @endif
                            </dd>
                            @if($template->ApprovalReason)
                                <dt class="col-5">Approval Reason</dt><dd class="col-7">{{ $template->ApprovalReason }}</dd>
                            @endif
                            <dt class="col-5">Created</dt><dd class="col-7">{{ \Carbon\Carbon::parse($template->CreatedOn)->format('Y-m-d H:i') }}</dd>
                            <dt class="col-5">Modified</dt><dd class="col-7">{{ $template->ModifiedOn ? \Carbon\Carbon::parse($template->ModifiedOn)->format('Y-m-d H:i') : '—' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection
