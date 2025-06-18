@extends('layouts.app')
@section('title', 'Edit Category')
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
    <h1>Edit Floor</h1>
    <form action="{{ route('addfloor.update', $floor->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="PropertyID" class="form-label">Property Name</label>
            <select name="PropertyID" class="form-select" required>
              <option value="#">--Select a property--</option>
              @foreach ($properties as $property)
                <option value="{{ $property->Id }}">{{ $property->PropertyName }}</option>
              @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="BlockName" class="form-label">Block Name</label>
            <select name="BlockID" class="form-select" required>
              <option value="#">--Select a block--</option>
              @foreach ($blocks as $block)
                <option value="{{ $block->Id }}">{{ $block->BlockName }}</option>
              @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="FloorLabel" class="form-label">Floor Name</label>
            <input type="text" name="FloorLabel" class="form-control" value="{{ old('FloorLabel', $floor->FloorLabel) }}" required>
        </div>
        <div class="mb-3">
            <label for="FloorNotes" class="form-label">Notes</label>
            <textarea name="FloorNotes" class="form-control" rows="4">{{ old('FloorNotes', $floor->FloorNotes) }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Update Floor</button>
        <a href="{{ route('addfloor.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
@endsection
