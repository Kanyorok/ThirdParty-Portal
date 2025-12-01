@extends('layouts.app')
@section('title', 'Tenant Details')
@section('content')
<div class="container mt-4" style="max-width: 1000px;">

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">
            {{-- Tenant Information --}}
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tenant Type</label>
                    <input type="text" class="form-control bg-light" value="{{ $newtenant->type->Description ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tenant Name</label>
                    <input type="text" class="form-control bg-light"
                           value="{{ $newtenant->thirdParty->ThirdPartyName  ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">ID / Registration No.</label>
                    <input type="text" class="form-control bg-light"
                           value="{{ $newtenant->thirdParty->RegistrationNumber ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Phone Number</label>
                    <input type="text" class="form-control bg-light" value="{{ $newtenant->thirdParty->Phone ?? '-' }}"
                           readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email Address</label>
                    <input type="text" class="form-control bg-light" value="{{ $newtenant->thirdParty->Email ?? '-' }}"
                           readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Country of Origin</label>
                    <input type="text" class="form-control bg-light"
                           value="{{ $newtenant->thirdParty->Country ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Postal Address</label>
                    <input type="text" class="form-control bg-light"
                           value="{{ $newtenant->thirdParty->PhysicalAddress ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Status</label>
                    <div class="form-control bg-light">
                        @if($newtenant->IsActive == 1)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label">Attached Documents</label>
                    <div class="p-2 border rounded bg-light">
                        @forelse($newtenant->documents()->get(['t_Documents.Id', 't_Documents.DocumentId','MimeType','Name']) as $document)
                            {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
                        @empty
                            <span class="text-muted">No documents attached.</span>
                        @endforelse
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Remarks</label>
                    <textarea class="form-control bg-light" rows="2" readonly>{{ $newtenant->Remarks ?? '-' }}</textarea>
                </div>
            </div>
        </div>

        {{-- Footer with Audit Info and Actions --}}
        <div class="card-footer d-flex justify-content-between align-items-center py-2 bg-light small text-muted">
            <div>
                Created by: <strong>{{ $newtenant->createdByUser->Name ?? '-' }}</strong>
                on <strong>{{ $newtenant->CreatedOn ? \Carbon\Carbon::parse($newtenant->CreatedOn)->format('d M Y') : '-' }}</strong>
                | Modified by: <strong>{{ $newtenant->modifiedByUser->Name ?? '-' }}</strong>
                on <strong>{{ $newtenant->ModifiedOn ? \Carbon\Carbon::parse($newtenant->ModifiedOn)->format('d M Y') : '-' }}</strong>
            </div>

            <div>
                <a href="{{ route('addtenant.edit', $newtenant->Id) }}" class="btn btn-sm btn-primary">Edit</a>
                <a href="{{ route('addtenant.index') }}" class="btn btn-sm btn-secondary">Back</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @include('snippets.actions.preview-files')
@endsection
