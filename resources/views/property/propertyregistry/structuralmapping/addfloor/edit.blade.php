@extends('layouts.app')
@section('title', 'Floors Per Block')
@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('addfloor.update', $floor->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card shadow">
            <div class="card-header bg-light fw-bold">Floor Setup</div>
            <div class="card-body">

                {{-- Property (readonly) --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Property</label>
                        <input type="hidden" name="PropertyID" value="{{ $floor->PropertyID }}">
                        <input type="text" class="form-control"
                               value="{{ $floor->property->PropertyName ?? '' }}" readonly>
                    </div>
                </div>

                {{-- Block (readonly) --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Block</label>
                        <input type="hidden" name="BlockID" value="{{ $floor->BlockID }}">
                        <input type="text" class="form-control"
                               value="{{ $floor->block->BlockName ?? '' }}" readonly>
                    </div>
                </div>

                {{-- Floor Name --}}
                <div class="mb-3">
                    <label for="FloorLabel" class="form-label">Floor Name</label>
                    <input type="text" name="FloorLabel" class="form-control"
                           value="{{ old('FloorLabel', $floor->FloorLabel) }}" required>
                </div>

                {{-- Floor Notes --}}
                <div class="mb-3">
                    <label for="FloorNotes" class="form-label">Notes</label>
                    <textarea name="FloorNotes" class="form-control" rows="4">{{ old('FloorNotes', $floor->FloorNotes) }}</textarea>
                </div>

                {{-- Buttons --}}
                <button type="submit" class="btn btn-success">Update Floor</button>
                <a href="{{ route('addfloor.index') }}" class="btn btn-secondary">Cancel</a>

            </div>
        </div>
    </form>
@endsection
