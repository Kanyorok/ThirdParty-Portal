@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Tender Type</h2>
    
    <form action="{{ route('tender-types.update', $tenderType->Id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="TypeCode">Type Code</label>
            <input type="text" class="form-control" id="TypeCode" name="TypeCode" 
                   value="{{ $tenderType->TypeCode }}" required>
        </div>
        
        <div class="form-group">
            <label for="TenderType">Tender Type</label>
            <input type="text" class="form-control" id="TenderType" name="TenderType" 
                   value="{{ $tenderType->TenderType }}" required>
        </div>
        
        <div class="form-group">
            <label for="Description">Description</label>
            <textarea class="form-control" id="Description" name="Description" rows="3">{{ $tenderType->Description }}</textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('tender-types.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection