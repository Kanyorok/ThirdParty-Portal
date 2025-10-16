@extends('layouts.app')
@section('title', 'Edit Maintenance Request')

@section('content')
<div class="container mt-5" style="max-width: 800px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('maintenancerequest.index') }}" class="btn btn-outline-secondary btn-sm">← Back to List</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('maintenancerequest.update', $maintenancerequest->Id) }}" enctype="multipart/form-data"
          method="POST">
        @csrf
        @method('PUT')

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Request Number<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" value="{{ $maintenancerequest->RequestNumber }}" disabled>
                </div>

                    {{-- Property --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Property<span class="text-danger">*</span></label>
                        <select name="Property" id="property-select"
                                class="form-select @error('Property') is-invalid @enderror" required>
                            <option value="">-- Select Property --</option>
                            @foreach($properties as $property)
                                <option
                                    value="{{ $property->Id }}" {{ $property->Id == old('Property', $maintenancerequest->Property) ? 'selected' : '' }}>
                                    {{ $property->PropertyName }}
                                </option>
                            @endforeach
                        </select>
                        @error('Property')
                        <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Block --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Block</label>
                        <select name="Block" id="block-select" class="form-select @error('Block') is-invalid @enderror">
                            <option value="">-- Select Block --</option>
                            @if($maintenancerequest->block)
                                <option value="{{ $maintenancerequest->Block }}"
                                        selected>{{ $maintenancerequest->block->BlockName }}</option>
                            @endif
                        </select>
                        @error('Block')
                        <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Floor --}}
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Floor</label>
                        <select name="Floor" id="floor-select" class="form-select @error('Floor') is-invalid @enderror">
                            <option value="">-- Select Floor --</option>
                            @if($maintenancerequest->floor)
                                <option value="{{ $maintenancerequest->Floor }}"
                                        selected>{{ $maintenancerequest->floor->FloorLabel }}</option>
                            @endif
                        </select>
                        @error('Floor')
                        <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Unit --}}
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Unit</label>
                        <select name="Unit" id="unit-select" class="form-select @error('Unit') is-invalid @enderror">
                            <option value="">-- Select Unit --</option>
                            @if($maintenancerequest->unit)
                                <option value="{{ $maintenancerequest->Unit }}"
                                        selected>{{ $maintenancerequest->unit->UnitCode }}</option>
                            @endif
                        </select>
                        @error('Unit')
                        <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Request Details --}}
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Reported By<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="ReportedBy" placeholder="Optional"
                               value="{{ old('ReportedBy', $maintenancerequest->ReportedBy) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Issue Type<span class="text-danger">*</span></label>
                        <select class="form-select" name="IssueType" required>
                            <option value="">-- Select Issue Type --</option>
                            @foreach ($issuetypes as $issuetype)
                                <option value="{{ $issuetype->ID }}"
                                    {{ $issuetype->ID == old('IssueType', $maintenancerequest->IssueType) ? 'selected' : '' }}>
                                    {{ $issuetype->Description }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Priority<span class="text-danger">*</span></label>
                        <select class="form-select" name="Priority" required>
                            <option value="">-- Select Priority Level --</option>
                            @foreach ($priorities as $priority)
                                <option value="{{ $priority->ID }}"
                                    {{ $priority->ID == old('Priority', $maintenancerequest->Priority) ? 'selected' : '' }}>
                                    {{ $priority->Description }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Issue Description --}}

                <div class="mb-3">
                    <label class="form-label">Issue Description</label>
                    <textarea name="IssueDescription" class="form-control" rows="3" placeholder="Optional">{{ old('IssueDescription', $maintenancerequest->IssueDescription) }}</textarea>
                </div>

                <!-- Document Upload -->

                <div class="col-12">
                    <label class="form-label">Attached Documents</label>
                    <div class="p-2 border rounded bg-light">
                        @forelse($maintenancerequest->documents()->get(['t_Documents.Id', 't_Documents.DocumentId','MimeType','Name']) as $document)
                            {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
                        @empty
                            <span class="text-muted">No documents attached.</span>
                        @endforelse
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Upload Relevant Documents</label>
                    <input type="file" name="Document[]" class="form-control" multiple>
                </div>

            </div>

            <div class="card-footer bg-light d-flex justify-content-between">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-save"></i> Update Maintenance Request
                </button>
                <a href="{{ route('maintenancerequest.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>

<script>
const routes = {
    getBlocks: "{{ route('getblockbyproperty.maintenance', ['PropertyId' => '__ID__']) }}",
    getFloors: "{{ route('getfloorbyblock.maintenance', ['BlockId' => '__ID__']) }}",
    getUnits: "{{ route('getunitbyfloor.maintenance', ['FloorId' => '__ID__']) }}"
};

document.addEventListener('DOMContentLoaded', function () {
    const propertySelect = document.getElementById('property-select');
    const blockSelect = document.getElementById('block-select');
    const floorSelect = document.getElementById('floor-select');
    const unitSelect = document.getElementById('unit-select');

    // Pre-populate selects on page load
    function loadBlocks(propertyId, selectedBlockId = null) {
        blockSelect.innerHTML = '<option value="">-- Select Block --</option>';
        if (propertyId) {
            fetch(routes.getBlocks.replace('__ID__', propertyId))
                .then(res => res.json())
                .then(data => {
                    data.forEach(block => {
                        const option = document.createElement('option');
                        option.value = block.Id;
                        option.textContent = block.BlockName;
                        if (selectedBlockId && block.Id == selectedBlockId) option.selected = true;
                        blockSelect.appendChild(option);
                    });
                });
        }
    }
    function loadFloors(blockId, selectedFloorId = null) {
        floorSelect.innerHTML = '<option value="">-- Select Floor --</option>';
        if (blockId) {
            fetch(routes.getFloors.replace('__ID__', blockId))
                .then(res => res.json())
                .then(data => {
                    data.forEach(floor => {
                        const option = document.createElement('option');
                        option.value = floor.Id;
                        option.textContent = floor.FloorLabel;
                        if (selectedFloorId && floor.Id == selectedFloorId) option.selected = true;
                        floorSelect.appendChild(option);
                    });
                });
        }
    }
    function loadUnits(floorId, selectedUnitId = null) {
        unitSelect.innerHTML = '<option value="">-- Select Unit --</option>';
        if (floorId) {
            fetch(routes.getUnits.replace('__ID__', floorId))
                .then(res => res.json())
                .then(data => {
                    data.forEach(unit => {
                        const option = document.createElement('option');
                        option.value = unit.Id;
                        option.textContent = unit.UnitCode;
                        if (selectedUnitId && unit.Id == selectedUnitId) option.selected = true;
                        unitSelect.appendChild(option);
                    });
                });
        }
    }

    // Initial load with current values
    const initialProperty = "{{ $maintenancerequest->Property }}";
    const initialBlock = "{{ $maintenancerequest->Block }}";
    const initialFloor = "{{ $maintenancerequest->Floor }}";
    const initialUnit = "{{ $maintenancerequest->Unit }}";
    if (initialProperty) loadBlocks(initialProperty, initialBlock);
    if (initialBlock) loadFloors(initialBlock, initialFloor);
    if (initialFloor) loadUnits(initialFloor, initialUnit);

    propertySelect.addEventListener('change', function () {
        loadBlocks(this.value);
        floorSelect.innerHTML = '<option value="">-- Select Floor --</option>';
        unitSelect.innerHTML = '<option value="">-- Select Unit --</option>';
    });

    blockSelect.addEventListener('change', function () {
        loadFloors(this.value);
        unitSelect.innerHTML = '<option value="">-- Select Unit --</option>';
    });

    floorSelect.addEventListener('change', function () {
        loadUnits(this.value);
    });
});
</script>
@endsection

@section('scripts')
    @include('snippets.actions.preview-files')
@endsection
