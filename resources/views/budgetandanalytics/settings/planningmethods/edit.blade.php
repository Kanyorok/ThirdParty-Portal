@extends('layouts.app')

@section('title', 'Edit Budget Planning Method')

@section('content')
    <div class="card p-4">
        <h5>✏️ Edit Budget Planning Method</h5>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('planningmethods.update', $method->Id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="MethodName" class="form-label">Method Name</label>
                <input type="text" name="MethodName" id="MethodName" class="form-control" required
                       value="{{ old('MethodName', $method->MethodName) }}">
            </div>

            <div class="mb-3">
                <label for="Description" class="form-label">Description</label>
                <textarea name="Description" id="Description" class="form-control"
                          rows="3">{{ old('Description', $method->Description) }}</textarea>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="IsActive" id="IsActive" value="1"
                    {{ old('IsActive', $method->IsActive) ? 'checked' : '' }}>
                <label class="form-check-label" for="IsActive">Set as Active</label>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary"
                        onclick="this.disabled=true; this.innerText='Updating...'; this.form.submit();">💾 Update
                </button>
                <a href="{{ route('planningmethods.index') }}" class="btn btn-secondary">↩️ Cancel</a>
            </div>
        </form>
    </div>
@endsection
