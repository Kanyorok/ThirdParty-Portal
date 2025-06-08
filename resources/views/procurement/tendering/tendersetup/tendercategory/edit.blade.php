@extends('layouts.app')
@section('title', 'Edit Tender Category')
@section('content')
<div class="container">
    <h2>Edit Category</h2>
    
    <form action="{{ route('tendercategory.update', $tenderCategory->Id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="catCode">Category Code</label>
            <input type="text" class="form-control" id="catCode" name="catCode" 
                   value="{{ $tenderCategory->CategoryCode }}" readonly required>
        </div>
        
        <div class="form-group">
            <label for="TenderCategory">Tender Type</label>
            <input type="text" class="form-control" id="TenderCategory" name="TenderCategory" 
                   value="{{ $tenderCategory->TenderCategory }}" required>
            @error('TenderCategory')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="categoryDescription">Description</label>
            <textarea class="form-control" id="categoryDescription" name="Description" rows="3">{{ $tenderCategory->Description }}</textarea>
            @error('Description')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>
        
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('tendercategory.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection