@extends('layouts.app')
@section('title', 'Edit Legal Search Request')

@section('content')
<div class="card p-1 shadow rounded-4">
    <div class="card-body">
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        <p class="text-muted">
            Update the details of this legal search request. You can modify the request type, entity name, or
            remarks as needed.
        </p>

        <form method="POST" action="{{ route('legal.search_requests.update', $request->Id) }}">
            @csrf
            @method('PUT')

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Request Type</label>
                    <select name="RequestType" class="form-select" required>
                        <option disabled value="">-- Select Request Type --</option>
                        @foreach($details as $item)
                        <option value="{{ $item->Value }}"
                            {{ $item->Value == $request->RequestType ? 'selected' : '' }}>
                            {{ $item->Value }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Entity Name</label>
                    <input type="text" name="EntityName"
                        class="form-control"
                        value="{{ old('EntityName', $request->EntityName) }}"
                        required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Remarks</label>
                <textarea name="Remarks" rows="3" class="form-control"
                    required>{{ old('Remarks', $request->Remarks) }}</textarea>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-3">
                <a href="{{ route('legal.search_requests.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-long-arrow-alt-left"></i> Back
                </a>
                <button type="submit" class="btn btn-info"
                    onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Updating...'; this.form.submit();}">
                    <i class="fas fa-save"></i> Update Request
                </button>
            </div>
        </form>
    </div>
</div>
@endsection