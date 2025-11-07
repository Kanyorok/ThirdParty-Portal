@extends('layouts.app')
@section('title', 'Add Unit of Measure (UOM)')
@section('content')

<div class="container mt-4">
    <div class="card shadow rounded-4">
        <div class="card-header text-dark rounded-top-4" style="background-color: #add8e6;">
            <h4 class="mb-0">Add Unit of Measure (UOM)</h4>
        </div>
        <div class="card-body">

            <form action="{{ route('unitofmeasure.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="Code" class="form-label">UOM Code</label>
                    <input type="text" class="form-control @error('Code') is-invalid @enderror" id="Code" name="Code" 
                           value="{{ old('Code') }}" placeholder="e.g., PCS" required>
                    @error('Code')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="Name" class="form-label">UOM Name</label>
                    <input type="text" class="form-control @error('Name') is-invalid @enderror" id="Name" name="Name" 
                           value="{{ old('Name') }}" placeholder="e.g., Pieces" required>
                    @error('Name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-check mb-2">
                    <input type="hidden" name="BaseUnit" value="0">
                    <input class="form-check-input @error('BaseUnit') is-invalid @enderror" type="checkbox" name="BaseUnit" value="1" id="BaseUnit" 
                           {{ old('BaseUnit', 1) ? 'checked' : '' }}>
                    <label class="form-check-label" for="BaseUnit">Base Unit?</label>
                    @error('BaseUnit')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-check mb-3">
                    <input type="hidden" name="Active" value="0">
                    <input class="form-check-input @error('Active') is-invalid @enderror" type="checkbox" name="Active" value="1" id="Active" 
                           {{ old('Active', 1) ? 'checked' : '' }}>
                    <label class="form-check-label" for="Active">Is Active?</label>
                    @error('Active')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Save</button>
                    <a href="{{ route('unitofmeasure.index') }}" class="btn btn-danger px-4 ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection