@extends('layouts.app')

@section('title', 'SKU Details')

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
  <div class="card shadow rounded-4">
    <div class="card-header text-dark rounded-top-4" style="background-color: #add8e6;">
      <h4 class="mb-0">Store Details</h4>
    </div>

<div class="container bg-white shadow-sm rounded p-4">


    <div class="row mb-2">
        <div class="col-md-4">
            <p><strong>Store ID:</strong> {{ $store->StoreID }}</p>
        </div>
        <div class="col-md-4">
            <p><strong>Store Name:</strong> {{ $store->StoreName }}</p>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-md-4">
        <p><strong>Branch:</strong> {{ $store->branch->Name }}</p>
        </div>
        <div class="col-md-4">
            <p><strong>Status:</strong> {!! $store->Status ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' !!}</p>
        </div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <a href="{{ route('stores.index') }}" class="btn btn-secondary">Back</a>
        <a href="{{ route('stores.edit', $store->Id) }}" class="btn btn-warning">Edit SKU</a>
        <form action="{{ route('stores.destroy', $store->Id) }}" method="POST" onsubmit="return confirm('⚠️ Are you sure you want to delete this SKU?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Delete SKU</button>
        </form>
        
    </div>
</div>

@endsection
