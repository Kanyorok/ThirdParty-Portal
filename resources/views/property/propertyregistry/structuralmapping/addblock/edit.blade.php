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
    <h1>Edit Block</h1>
    <form action="{{ route('addblock.update', $block->Id) }}" method="POST">
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
            <input type="text" name="BlockName" class="form-control" value="{{ old('BlockName', $block->BlockName) }}" required>
        </div>
        <div class="mb-3">
            <label for="Description" class="form-label">Description </label>
            <textarea name="Description" class="form-control" rows="4">{{ old('Description', $block->Description) }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Update Block</button>
        <a href="{{ route('addblock.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
@endsection
