
@extends('layouts.app')
@section('title', 'Add Inventory Type')
@section('content')
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <h4>Add Inventory Type</h4>
    <form action="{{ route('inventorytype.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
    <div class="mb-3">
        <label for="Type" class="form-label">Inventory Type</label>
        <input type="text" class="form-control" id="Type" name="Type" placeholder="e.g., Asset">
    </div>
    <input type="hidden" name="Active" value="1">
                <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Save</button>
    </form>
</div>

@endsection
