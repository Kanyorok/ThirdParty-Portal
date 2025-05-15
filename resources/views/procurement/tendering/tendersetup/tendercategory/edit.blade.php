@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Category</h2>
    
    <form action="{{ route('tender-categories.update', $tenderCategory->Id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="catCode">Category Code</label>
            <input type="text" class="form-control" id="catCode" name="catCode" 
                   value="{{ $tenderCategory->catCode }}" required>
        </div>
        
        <div class="form-group">
            <label for="TenderCategory">Tender Type</label>
            <input type="text" class="form-control" id="TenderCategory" name="TenderCategory" 
                   value="{{ $tenderCategory->TenderCategory }}" required>
        </div>
        
        <div class="form-group">
            <label for="categoryDescription">Description</label>
            <textarea class="form-control" id="categoryDescription" name="categoryDescription" rows="3">{{ $tenderCategory->Description }}</textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('tender-categories.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection