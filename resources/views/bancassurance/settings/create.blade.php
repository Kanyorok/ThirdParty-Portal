@extends('layouts.app')
@section('title', isset($codeDetail) ? 'Edit Setting' : 'Add Setting')

@section('content')
    <div class="container mt-4">
        <h4>{{ isset($codeDetail) ? '✏️ Edit Setting' : '➕ Add New Setting' }}</h4>

        <form method="POST" action="{{ route('bancassurance.settings.store_or_update') }}">
            @csrf

            @if(isset($codeDetail))
                <input type="hidden" name="id" value="{{ $codeDetail->ID }}">
            @endif

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="CodeID" class="form-label">Code Type</label>
                    <input type="text" class="form-control" name="CodeID" id="CodeID"
                           value="{{ old('CodeID', $codeDetail->CodeID ?? request('code_id')) }}"
                           {{ isset($codeDetail) ? 'readonly' : '' }} required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="Description" class="form-label">Description</label>
                    <input type="text" class="form-control" name="Description" id="Description"
                           value="{{ old('Description', $codeDetail->Description ?? '') }}" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="DisplayOrder" class="form-label">Display Order</label>
                    <input type="number" class="form-control" name="DisplayOrder" id="DisplayOrder"
                           value="{{ old('DisplayOrder', $codeDetail->DisplayOrder ?? '') }}">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="IsActive" class="form-label">Status</label>
                    <select name="IsActive" id="IsActive" class="form-select" required>
                        <option value="1" {{ old('IsActive', $codeDetail->IsActive ?? 1) == 1 ? 'selected' : '' }}>
                            Active
                        </option>
                        <option value="0" {{ old('IsActive', $codeDetail->IsActive ?? 1) == 0 ? 'selected' : '' }}>
                            Inactive
                        </option>
                    </select>
                </div>
            </div>

            <div class="text-end">
                <a href="{{ route('bancassurance.settings.index', ['code_id' => $codeDetail->CodeID ?? request('code_id')]) }}"
                   class="btn btn-secondary">🔙 Cancel</a>
                <button type="submit" class="btn btn-primary">💾 Save Setting</button>
            </div>
        </form>
    </div>
@endsection
