@extends('layouts.app')
@section('title', 'Property Details')
@section('content')
<div class="container mt-4" style="max-width: 900px;">
      
    <div class="card shadow-sm rounded-3">
        <div class="card-body">
            <form>
                <div class="row g-3">
                    {{-- Property fields --}}
                    <div class="col-md-6">
                        <label class="form-label">Property Name</label>
                        <input type="text" class="form-control" value="{{ $property->PropertyName ?? '-' }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Property Code</label>
                        <input type="text" class="form-control" value="{{ $property->PropertyCode ?? '-' }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Property Type</label>
                        <input type="text" class="form-control" value="{{ $property->type->PropertyTypeName ?? '-' }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Property Category</label>
                        <input type="text" class="form-control" value="{{ $property->propertyCategory->Name ?? '-' }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Owner</label>
                        <input type="text" class="form-control" value="{{ $property->Owner ?? '-' }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Acquisition Date</label>
                        <input type="text" class="form-control" value="{{ $property->AcquisitionDate ? \Carbon\Carbon::parse($property->AcquisitionDate)->format('d/m/Y') : '-' }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Country</label>
                        <input type="text" class="form-control" value="{{ $property->Country ?? '-' }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Town/City</label>
                        <input type="text" class="form-control" value="{{ $property->propertyLocality->Name ?? '-' }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Area/Locality</label>
                        <input type="text" class="form-control" value="{{ $property->AreaLocality ?? '-' }}" readonly>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Property Description</label>
                        <textarea class="form-control" rows="3" readonly>{{ $property->PropertyDescription ?? '-' }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Attached Documents</label>
                        <div class="p-2 border rounded bg-light">
                            @forelse($property->documents()->get(['t_Documents.Id', 't_Documents.DocumentId','MimeType','Name']) as $document)
                                {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
                            @empty
                                <span class="text-muted">No documents attached.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Footer Section with Audit Info + Back Button --}}
        <div class="card-footer small bg-light text-muted">
            <div class="row g-2">
                <div class="col-md-3">
                    <strong>Created By:</strong> {{ $property->createdByUser->Name ?? '-' }}
                </div>
                <div class="col-md-3">
                    <strong>Created On:</strong> {{ $property->CreatedOn ? \Carbon\Carbon::parse($property->CreatedOn)->format('d/m/Y') : '-' }}
                </div>
                <div class="col-md-3">
                    <strong>Modified By:</strong> {{ $property->modifiedByUser->Name ?? '-' }}
                </div>
                <div class="col-md-3">
                    <strong>Modified On:</strong> {{ $property->ModifiedOn ? \Carbon\Carbon::parse($property->ModifiedOn)->format('d/m/Y') : '-' }}
                </div>
            </div>

            <div class="text-end mt-2">
                <a href="{{ route('PropertyRegistry.index') }}" class="btn btn-sm btn-secondary">⬅ Back</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
 @include('snippets.actions.preview-files')
@endsection
