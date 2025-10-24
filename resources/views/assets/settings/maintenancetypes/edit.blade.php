@extends('layouts.app')
@section('title','Edit Maintenance Type')

@section('content')
<div class="container my-3">
  <div class="card shadow-sm rounded-3">
    <div class="card-header bg-light py-2 px-3"><h6 class="mb-0 text-muted">Edit Maintenance Type</h6></div>
    <div class="card-body p-3">
      @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
      <form method="post" action="{{ route('assets.settings.maintenance-types.update',$row->Id) }}">
        @csrf @method('PUT')
        <div class="row g-3">
          <div class="col-md-3"><label class="form-label">Code</label><input name="Code" value="{{ old('Code',$row->Code) }}" class="form-control form-control-sm" required></div>
          <div class="col-md-6"><label class="form-label">Name</label><input name="Name" value="{{ old('Name',$row->Name) }}" class="form-control form-control-sm" required></div>
          <div class="col-md-2 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="IsActive" id="act" @checked(old('IsActive',$row->IsActive))><label class="form-check-label" for="act">Active</label></div></div>
        </div>
        <div class="mt-3"><a href="{{ route('assets.settings.maintenance-types.index') }}" class="btn btn-outline-secondary btn-sm">Back</a><button class="btn btn-primary btn-sm ms-2">Update</button></div>
      </form>
    </div>
  </div>
</div>
@endsection
