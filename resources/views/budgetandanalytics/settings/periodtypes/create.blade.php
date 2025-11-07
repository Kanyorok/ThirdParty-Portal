@extends('layouts.app')
@section('title', 'Create Budget Period Type')
@section('content')

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card p-4">
        <h5>➕ Add Budget Period Type</h5>

        <form action="{{ route('periodtypes.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="PeriodType" class="form-label">Period Type</label>
                <input type="text" name="PeriodType" id="PeriodType" class="form-control" placeholder="e.g., Annually"
                       value="{{ old('PeriodType') }}" required>
            </div>

            <div class="mb-3">
                <label for="Code" class="form-label">Code</label>
                <input type="text" name="Code" id="Code" class="form-control" placeholder="e.g., ANL"
                       value="{{ old('Code') }}" required>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" name="IsActive" id="IsActive" class="form-check-input" value="1" checked>
                <label class="form-check-label" for="IsActive">Mark as Active</label>
            </div>

            <button type="submit" class="btn btn-success"
                    onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">💾 Save
            </button>
            <a href="{{ route('periodtypes.index') }}" class="btn btn-secondary">🔙 Back</a>
        </form>
    </div>
@endsection
