@extends('layouts.app')
@section('title', 'Edit Budget Rate Type')

@section('content')
<div class="card p-4">
    <h5 class="mb-4">✏️ Edit Budget Rate Type</h5>

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

    <form method="POST" action="{{ route('rates.update', $rate->Id) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="RateTypeCode" class="form-label">Rate Type Code</label>
            <input type="text" name="RateTypeCode" id="RateTypeCode" class="form-control" value="{{ old('RateTypeCode', $rate->RateTypeCode) }}" required>
        </div>

        <div class="mb-3">
            <label for="RateTypeName" class="form-label">Rate Type Name</label>
            <input type="text" name="RateTypeName" id="RateTypeName" class="form-control" value="{{ old('RateTypeName', $rate->RateTypeName) }}" required>
        </div>

        <div class="mb-3">
            <label for="Description" class="form-label">Description</label>
            <textarea name="Description" id="Description" class="form-control" rows="3">{{ old('Description', $rate->Description) }}</textarea>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="IsDefault" id="IsDefault" class="form-check-input" value="1"
                {{ old('IsDefault', $rate->IsDefault) ? 'checked' : '' }}>
            <label for="IsDefault" class="form-check-label">Set as Default Rate</label>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-primary">💾 Update</button>
            <a href="{{ route('rates.index') }}" class="btn btn-secondary">↩️ Cancel</a>
        </div>
    </form>
</div>
@endsection
