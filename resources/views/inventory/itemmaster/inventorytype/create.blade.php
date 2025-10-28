@extends('layouts.app')
@section('title', 'Add Inventory Type')
@section('content')

<div class="container mt-5">
    <div class="card shadow rounded-4">
        <div class="card-header text-dark rounded-top-4" style="background-color: #add8e6;">
            <h4 class="mb-0">Add Inventory Type</h4>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('inventorytype.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-3">
                    <label for="Type" class="form-label">Inventory Type</label>
                    <input type="text" class="form-control @error('Type') is-invalid @enderror" id="Type" name="Type" 
                           value="{{ old('Type') }}" placeholder="e.g., Asset" required>
                    @error('Type')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Hidden field for Active status -->
                <input type="hidden" name="Status" value="1">
                
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Save</button>
                    <a href="{{ route('inventorytype.index') }}" class="btn btn-danger px-4 ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection