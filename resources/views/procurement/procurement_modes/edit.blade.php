@extends('layouts.app')

@section('title','Edit Procurement Mode')

@section('content')
<div class="container mt-4">
    <h3 class="mb-4">Edit Procurement Mode</h3>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger">
        <strong>There were some problems with your input:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('procurement-modes.update', $procurement_mode->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Mode Name</label>
            <input type="text" name="name" id="name" value="{{ old('name', $procurement_mode->Name) }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description (Optional)</label>
            <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $procurement_mode->Description) }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update Mode</button>
        <a href="{{ route('procurement-modes.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection