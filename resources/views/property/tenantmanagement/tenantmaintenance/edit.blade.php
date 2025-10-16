@extends('layouts.app')
@section('title', 'Edit Tenant Details')
@section('content')
    <div class="container mt-4">

        <form action="{{ route('addtenant.update', $tenants->Id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <p><small>This screen is used in editing the tenant creation. (Tenant Profile is edited from the Third Party
                    Portal) </small></p>

            <div class="card shadow">
                <div class="card-header bg-light fw-bold">Tenant</div>
                <div class="card-body">

                    <!-- Tenant -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-5">
                            <label class="form-label">Tenant<span class="text-danger">*</span></label>
                            <input type="hidden" name="ThirdPartyId" value="{{ $tenants->ThirdPartyId }}">
                            <input type="text" class="form-control"
                                   value="{{ $tenants->thirdParty->ThirdPartyName }}" readonly>
                            <small class="text-muted">Tenant cannot be changed once created.</small>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Tenant Type<span class="text-danger">*</span></label>
                            <select class="form-select" name="TenantType" required>
                                @foreach ($tenantTypes as $tenanttype)
                                    <option value="{{ $tenanttype->ID }}"
                                        {{ old('TenantType', $tenants->TenantType) == $tenanttype->ID ? 'selected' : '' }}>
                                        {{ $tenanttype->Description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Status<span class="text-danger">*</span></label>
                            <select class="form-select" name="IsActive" required>
                                <option value="1" {{ $tenants->IsActive ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ !$tenants->IsActive ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Existing Documents -->
                    <div class="col-12 mb-3">
                        <label class="form-label">Attached Documents</label>
                        <div class="p-2 border rounded bg-light">
                            @forelse($tenants->documents as $document)
                                {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
                            @empty
                                <span class="text-muted">No documents attached.</span>
                            @endforelse
                        </div>
                    </div>

                    <!-- Upload Documents -->
                    <div class="mb-3">
                        <label class="form-label">Upload Supporting Documents</label>
                        <input type="file" name="Documents[]" class="form-control" multiple>
                        <small class="text-muted">Leave blank if no new documents are needed.</small>
                    </div>

                    <!-- Remarks -->
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="Remarks" class="form-control" rows="3"
                                  placeholder="Optional">{{ old('Remarks', $tenants->Remarks) }}</textarea>
                    </div>

                </div>

                <div class="card-footer text-end py-2">
                    <a href="{{ route('addtenant.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-success"
                            onclick="this.disabled=true; this.innerText='Updating...'; this.form.submit();">Update
                        Tenant
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    @include('snippets.actions.preview-files')
@endsection
