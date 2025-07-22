@extends('layouts.app')
@section('title', 'Tax Type Setup')
@section('content')

<div clas= "container mt-4">
    <div class="card p-4">
        <div class="card-header bg-dark text-white">
            🏷️ Edit Tax Type
        </div>

        <div class="card-body">
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

            <p class="text-muted mt-0">
                Update the details of the tax type for your organization.
            </p>
            <form method="POST" action="{{ route('taxtypes.update', $taxType->Id) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Tax Type Name</label>
                    <input type="text" 
                    name="TaxTypeName" 
                    class="form-control @error('TaxTypeName') is-invalid @enderror" 
                    value="{{ old('TaxTypeName', $taxType->TaxTypeName) }}" 
                    placeholder="e.g., VAT, Income Tax" required>
                    @error('TaxTypeName')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="Description" class="form-control" placeholder="Brief description of the tax type" required>{{ old('Description', $taxType->Description) }}</textarea>
                </div>
                <div class="d-flex justify-content-between align-content-center mt-4">
                    <a href="{{ route('taxtypes.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-success" onclick="if(this.form.checkValidity()){this.disabled=true; this.innerText='🔄 Updating...'; this.form.submit();}">🔄 Update Tax Type</button>
                </div>
            </form>
        </div>
    </div>
</div>  

@endsection