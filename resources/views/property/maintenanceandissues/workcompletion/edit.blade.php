@extends('layouts.app')
@section('title', 'Edit Maintenance Work Completion')

@section('content')
<div class="container mt-4">
    <form action="{{ route('workcompletion.update', $workCompletion->Id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card shadow">
            <div class="card-header bg-light fw-bold">Work Execution & Resolution</div>
            <div class="card-body">

                {{-- Maintenance Request --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Maintenance Request</label>
                    <input type="text" class="form-control" value="{{ $workCompletion->request->request->RequestNumber ?? '-' }}" readonly>
                    <input type="hidden" name="RequestNumber" value="{{ old('RequestNumber', $workCompletion->RequestNumber) }}">
                </div>

                {{-- Property Info --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Property</label>
                        <input type="text" class="form-control" value="{{ $workCompletion->request->request->property->PropertyName ?? '-' }}" readonly>
                        <input type="hidden" name="Property" value="{{ old('Property', $workCompletion->Property) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Block</label>
                        <input type="text" class="form-control" value="{{ $workCompletion->request->request->block->BlockName ?? '-' }}" readonly>
                        <input type="hidden" name="Block" value="{{ old('Block', $workCompletion->Block) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Floor</label>
                        <input type="text" class="form-control" value="{{ $workCompletion->request->request->floor->FloorLabel ?? '-' }}" readonly>
                        <input type="hidden" name="Floor" value="{{ old('Floor', $workCompletion->Floor) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Unit</label>
                        <input type="text" class="form-control" value="{{ $workCompletion->request->request->unit->UnitCode ?? '-' }}" readonly>
                        <input type="hidden" name="Unit" value="{{ old('Unit', $workCompletion->Unit) }}">
                    </div>
                </div>

                {{-- Parts, Cost, Final Status --}}
                <div class="row g-3 mb-3">
                <div class="col-md-3">
                        <label class="form-label">Completion Date</label>
                        <input type="date" class="form-control" name="CompletionDate" value="{{ old('CompletionDate', $workCompletion->CompletionDate) }}" required>
                        @error('CompletionDate') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Parts Used (Optional)</label>
                        <input type="text" class="form-control" name="PartsUsed" value="{{ old('PartsUsed', $workCompletion->PartsUsed) }}">
                        @error('PartsUsed') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cost</label>
                        <input type="number" class="form-control" name="Cost" value="{{ old('Cost', $workCompletion->Cost) }}">
                        @error('Cost') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Final Status</label>
                        <select class="form-select" name="FinalStatus" required>
                            <option value="">--Select a status--</option>
                            @foreach ($finalstatus as $status)
                                <option value="{{ $status->ID }}" {{ old('FinalStatus', $workCompletion->FinalStatus) == $status->ID ? 'selected' : '' }}>
                                    {{ $status->Description }}
                                </option>
                            @endforeach
                        </select>
                        @error('FinalStatus') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>
                </div>

                {{-- File Upload --}}
                <div class="mb-3">
                    <label class="form-label">Upload Resolution Evidence (Photos/Invoice)</label>
                    <input type="file" class="form-control" name="Document">
                    @error('Document') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                {{-- Work Summary --}}
                <div class="mb-3">
                    <label class="form-label">Work Done Summary</label>
                    <textarea class="form-control" rows="3" name="WorkDoneSummary" required>{{ old('WorkDoneSummary', $workCompletion->WorkDoneSummary) }}</textarea>
                    @error('WorkDoneSummary') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                {{-- Submit --}}
                <div class="text-end">
                    <button class="btn btn-primary" type="submit">Update Completion</button>
                    <a href="{{ route('workcompletion.index') }}" class="btn btn-secondary">Cancel</a>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection
