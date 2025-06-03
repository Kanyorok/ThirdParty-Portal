@extends('layouts.app')
@section('title', 'Patent Details')
@section('content')
<div class="container mt-5" style="max-width: 700px;">
  <h3 class="mb-4">Patent Details</h3>
  <div class="card">
    <div class="card-body">
      <dl class="row">
        <dt class="col-sm-4">PropertyID</dt>
        <dd class="col-sm-8">{{ $block->PropertyID?? '-' }}</dd>

        <dt class="col-sm-4">Block Name</dt>
        <dd class="col-sm-8">{{ $block->BlockName?? '-' }}</dd>

        <dt class="col-sm-4">Description</dt>
        <dd class="col-sm-8">{{ $block->Description ?? '-' }}</dd>
      </dl>
    </div>
    <div class="card-footer">
      <a href="#" class="btn btn-primary">Edit</a>
      <a href="#" class="btn btn-secondary">Back</a>
    </div>
  </div>
</div>
@endsection
