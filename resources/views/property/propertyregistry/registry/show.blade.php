@extends('layouts.app')
@section('title', 'Property Registry View')

@section('content')
    <div class="container mt-4" style="max-width: 1000px;">

        <div class="card shadow-lg border-0 rounded-3">
            {{-- Header --}}
            <div class="card-header text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Property Information</h5>
                <a href="{{ route('PropertyRegistry.index') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>

            {{-- Body --}}
        <div class="card-body">
            <form>
                <div class="row g-3">

                    {{-- Property Info --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Property Name</label>
                        <input type="text" class="form-control" value="{{ $property->PropertyName ?? '-' }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Property Code</label>
                        <input type="text" class="form-control" value="{{ $property->PropertyCode ?? '-' }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Property Type</label>
                        <input type="text" class="form-control" value="{{ $property->type->PropertyTypeName ?? '-' }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Property Category</label>
                        <input type="text" class="form-control" value="{{ $property->propertyCategory->Name ?? '-' }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Owner</label>
                        <input type="text" class="form-control" value="{{ $property->Owner ?? '-' }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Acquisition Date</label>
                        <input type="text" class="form-control"
                               value="{{ $property->AcquisitionDate ? \Carbon\Carbon::parse($property->AcquisitionDate)->format('d M Y') : '-' }}"
                               readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Country</label>
                        <input type="text" class="form-control" value="{{ $property->propertyCountry->Name ?? '-' }}"
                               readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Town/City</label>
                        <input type="text" class="form-control" value="{{ $property->propertyLocality->Name ?? '-' }}" readonly>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold">Area/Locality</label>
                        <input type="text" class="form-control" value="{{ $property->Address ?? '-' }}" readonly>
                    </div>

                    {{-- View Structure --}}
                    <div class="col-12 text-end mt-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#structureModal">
                            <i class="bi bi-diagram-3"></i> View Property Structure
                        </button>
                    </div>

                    {{-- Description --}}
                    <div class="col-12">
                        <label class="form-label fw-bold">Property Description</label>
                        <textarea class="form-control" rows="3" readonly>{{ $property->PropertyDescription ?? '-' }}</textarea>
                    </div>

                    {{-- Documents --}}
                    <div class="col-12">
                        <label class="form-label fw-bold">Attached Documents</label>
                        <div class="p-3 border rounded bg-light">
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

            {{-- Audit Info --}}
        <div class="card-footer small bg-light text-muted">
            <div class="row g-2">
                <div class="col-md-3">
                    <strong>Created By:</strong> {{ $property->createdByUser->Name ?? '-' }}
                </div>
                <div class="col-md-3">
                    <strong>Created On:</strong> {{ $property->CreatedOn ? \Carbon\Carbon::parse($property->CreatedOn)->format('d M Y') : '-' }}
                </div>
                <div class="col-md-3">
                    <strong>Modified By:</strong> {{ $property->modifiedByUser->Name ?? '-' }}
                </div>
                <div class="col-md-3">
                    <strong>Modified On:</strong> {{ $property->ModifiedOn ? \Carbon\Carbon::parse($property->ModifiedOn)->format('d M Y') : '-' }}
                </div>
            </div>
        </div>
        </div>
    </div>


    {{-- Property Structure Modal (Collapsible) --}}
    <div class="modal fade" id="structureModal" tabindex="-1" aria-labelledby="structureModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content shadow">
                <div class="modal-header text-white">
                    <h6 class="modal-title" id="structureModalLabel">
                        Property Structure - {{ $property->PropertyName }}
                        <p><small>click the blocks to view the floors</small></p>
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    @forelse($property->getBlockByProperty as $block)
                        {{-- Block --}}
                        <div class="mb-2">
                            <a class="text-primary d-block"
                               data-bs-toggle="collapse" href="#block-{{ $block->Id }}">
                                Block: {{ $block->BlockName ?? '-' }}
                            </a>


                            <div class="collapse ms-3 mt-2" id="block-{{ $block->Id }}">

                                <p><small>click the floor to view the units</small></p>
                                @forelse($block->floor as $floor)
                                    {{-- Floor --}}
                                    <a class="d-block text-primary ms-2"
                                       data-bs-toggle="collapse" href="#floor-{{ $floor->Id }}">
                                        Floor: {{ $floor->FloorLabel ?? '-' }}
                                    </a>

                                    <div class="collapse ms-4 mt-1" id="floor-{{ $floor->Id }}">
                                        <p class="text-muted small">{{ $floor->FloorNotes ?? '-' }}</p>

                                        @forelse($floor->units as $unit)
                                            {{-- Unit --}}
                                            <div class="ms-4 border rounded p-2 mb-1 bg-light">
                                                <span class="fw-bold">Unit: {{ $unit->UnitCode ?? '-' }}</span>
                                                <span class="ms-2"> Size: {{ $unit->UnitSize ?? '-' }}</span>
                                                <span class="ms-2">
                                        Availability:
                                        @if($unit->CurrentStatus)
                                                        <span class="badge bg-success">Vacant</span>
                                                    @else
                                                        <span class="badge bg-danger">Occupied</span>
                                                    @endif
                                    </span>
                                                <span class="ms-2">
                                        Rentable:
                                        @if($unit->IsRentable)
                                                        <span class="badge bg-success">Yes</span>
                                                    @else
                                                        <span class="badge bg-danger">No</span>
                                                    @endif
                                    </span>
                                            </div>
                                        @empty
                                            <div class="ms-4 text-muted small">No units found.</div>
                                        @endforelse
                                    </div>
                                @empty
                                    <div class="ms-3 text-muted">No floors found.</div>
                                @endforelse
                            </div>
                        </div>
                    @empty
                        <p class="text-muted">No blocks linked to this property.</p>
                    @endforelse

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Close
                    </button>
                </div>
            </div>
        </div>
</div>
@endsection

@section('scripts')
 @include('snippets.actions.preview-files')
@endsection
